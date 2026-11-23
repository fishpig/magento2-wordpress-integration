<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Post;

use Magento\Framework\Controller\ResultFactory;
use FishPig\WordPress\Model\Post;
use Magento\Framework\Exception\NoSuchEntityException;

class View extends \FishPig\WordPress\Controller\Action
{
    /**
     * @auto
     */
    protected $postRepository = null;

    /**
     * @auto
     */
    protected $seoMetaDataProvider = null;

    /**
     * @auto
     */
    protected $breadcrumbsDataProvider = null;

    /**
     * @auto
     */
    protected $customerSession = null;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \FishPig\WordPress\Controller\Action\Context $wpContext
     * @param \FishPig\WordPress\Model\PostRepository $postRepository,
     * @param \FishPig\WordPress\Api\Controller\Action\SeoMetaDataProviderInterface $seoMetaDataProvider
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Controller\Action\Context $wpContext,
        \FishPig\WordPress\Model\PostRepository $postRepository,
        \FishPig\WordPress\Api\Controller\Action\SeoMetaDataProviderInterface $seoMetaDataProvider,
        \FishPig\WordPress\Api\Controller\Action\BreadcrumbsDataProviderInterface $breadcrumbsDataProvider,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->postRepository = $postRepository;
        $this->seoMetaDataProvider = $seoMetaDataProvider;
        $this->breadcrumbsDataProvider = $breadcrumbsDataProvider;
        $this->customerSession = $customerSession;

        parent::__construct($context, $wpContext);
    }

    /**
     *
     */
    public function execute()
    {
        $request = $this->getRequest();

        try {
            $post = $this->postRepository->get((int)$request->getParam('id'));
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // The post does not exist
            // This may be that it does but it doesn't exist for this scope (WPML)
            $this->logger->warning($e->getMessage());
            return $this->getNoRouteForward();
        }

        $this->registry->register($post::ENTITY, $post);
        $this->registry->register(\FishPig\WordPress\Controller\Action::ENTITY_CURRENT, $post);

        if ($post->isFrontPage()) {
            // URL is post URL but this is front page so redirect to home URL
            if (!$this->url->doUrlsMatch($this->url->getHomeUrl())) {
                return $this->resultFactory->create(
                    ResultFactory::TYPE_REDIRECT
                )->setUrl(
                    $this->url->getHomeUrl()
                );
            }

            // Check for previews
            foreach (['p', 'page_id', 'preview_id'] as $paramKey) {
                if ($previewId = (int)$request->getParam($paramKey)) {
                    try {
                        $previewPost = $this->postRepository->get($previewId);

                        if (!$previewPost->isPublished()) {
                            $request->setParam('preview_id', $previewPost->getId());

                            $this->registry->unregister($previewPost::ENTITY);

                            return $this->resultFactory
                                ->create(
                                    \Magento\Framework\Controller\ResultFactory::TYPE_FORWARD
                                )->setModule(
                                    'wordpress'
                                )->setController(
                                    'post'
                                )->forward(
                                    'preview'
                                );
                        }
                    } catch (NoSuchEntityException $e) {
                        $previewPost = false;
                    }
                }
            }
        }

        // Only statuses the permalink router accepts are viewable. This blocks
        // drafts, pending, scheduled, trashed and revision posts loaded by ID.
        if (!in_array($post->getPostStatus(), ['publish', 'protected', 'private'], true)) {
            return $this->getNoRouteForward();
        }

        if ($post->getPostStatus() === 'private' && !$this->customerSession->isLoggedIn()) {
            return $this->getNoRouteForward();
        }

        // Check for comments
        // This could be improved, maybe using cookies in WP?
        $commentId = (int)$this->getRequest()->getParam('comment-id');
        $commentStatus = (int)$this->getRequest()->getParam('comment-status');
        $unapproved = (int)$this->getRequest()->getParam('unapproved');

        if ($unapproved > 0 || ($commentId > 0 && $commentStatus === 0)) {
            $this->messageManager->addSuccess(
                __('Your comment has been posted and is awaiting moderation.')
            );
        } elseif ($commentId > 0) {
            $this->messageManager->addSuccess(
                __('Your comment has been posted.')
            );
        }

        // We got here, we must be good.
        $resultPage = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
        );

        $this->addLayoutHandles($resultPage, $this->getLayoutHandles($post));

        $this->seoMetaDataProvider->addMetaData($resultPage, $post);

        if (!$post->isFrontPage()) {
            $this->addBreadcrumbs(
                $this->breadcrumbsDataProvider->getData($post)
            );
        }

        return $resultPage;
    }

    /**
     * @param  Post $post
     * @return array
     */
    private function getLayoutHandles(Post $post): array
    {
        $postType = $post->getPostType();
        $template = $post->getMetaValue('_wp_page_template');

        if ($postType == 'revision' && $post->getParentPost()) {
            $postType = $post->getParentPost()->getPostType();

            if (!$template) {
                $template = $post->getParentPost()->getMetaValue('_wp_page_template');
            }
        }

        $layoutHandles = ['wordpress_post_view_default'];

        if ($post->isFrontPage()) {
            $layoutHandles[] = 'wordpress_front_page';
        }

        $layoutHandles[] = 'wordpress_' . $postType . '_view';
        $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $post->getId();

        if ($template) {
            $templateName = str_replace('-', '_', str_replace('.php', '', $template));

            $layoutHandles[] = 'wordpress_post_view_' . $templateName;
            $layoutHandles[] = 'wordpress_post_view_' . $templateName . '_' . $post->getId();

            if ($postType !== 'post') {
                $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $templateName;
                $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $templateName . '_' . $post->getId();
            }
        }

        if ($post->getParentId()) {
            $layoutHandles[] = 'wordpress_' . $postType . '_view_parent_' . $post->getParentId();
        }

        if ($urlKey = preg_replace('/[^a-z0-9]+/', '_', strtolower(rtrim($post->getPermalink() ?? '', '/')))) {
            $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $urlKey;
        }

        if ((int)$this->getRequest()->getParam('preview_id') > 0 || $this->getRequest()->getParam('preview', '') === 'true') {
            $layoutHandles = array_merge(
                $layoutHandles,
                array_unique(
                    [
                        'wordpress_post_preview',
                        'wordpress_' . $postType . '_preview'
                    ]
                )
            );
        }

        return $layoutHandles;
    }
}
