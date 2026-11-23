<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\App\View\DirectiveFilter;

class Post extends AbstractMetaModel implements \FishPig\WordPress\Api\Data\ViewableModelInterface
{
    /**
     * @auto
     */
    protected $postTypeRepository = null;

    /**
     * @auto
     */
    protected $postRepository = null;

    /**
     * @auto
     */
    protected $taxonomyRepository = null;

    /**
     * @auto
     */
    protected $termRepository = null;

    /**
     * @auto
     */
    protected $userRepository = null;

    /**
     * @auto
     */
    protected $shortcodeFactory = null;

    /**
     * @auto
     */
    protected $termCollectionFactory = null;

    /**
     * @auto
     */
    protected $imageFactory = null;

    /**
     * @auto
     */
    protected $frontPage = null;

    /**
     * @auto
     */
    protected $dateHelper = null;

    /**
     * @auto
     */
    protected $passwordManager = null;

    /**
     * @const string
     */
    const ENTITY = 'wordpress_post';
    const CACHE_TAG = 'wordpress_post';

    /**
     * @const string
     */
    const RENDERED_CONTENT_META_KEY = '_post_content_rendered';
    const RENDERED_EXERPT_META_KEY = '_post_exerpt_rendered';

    /**
     * @var string
     */
    protected $_eventPrefix = 'wordpress_post';
    protected $_eventObject = 'post';

    /**
     * @var PostType
     */
    private $typeInstance = null;

    /**
     * 
     */
    private $customerSession;

    /**
     * @var \FishPig\WordPress\App\View\DirectiveFilter
     */
    private $directiveFilter;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Api\Data\MetaDataProviderInterface $metaDataProvider,
        \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository,
        \FishPig\WordPress\Model\PostRepository $postRepository,
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository,
        \FishPig\WordPress\Model\TermRepository $termRepository,
        \FishPig\WordPress\Model\UserRepository $userRepository,
        \FishPig\WordPress\Block\ShortcodeFactory $shortcodeFactory,
        \FishPig\WordPress\Model\ResourceModel\Term\CollectionFactory $termCollectionFactory,
        \FishPig\WordPress\Model\ImageFactory $imageFactory,
        \FishPig\WordPress\Helper\FrontPage $frontPage,
        \FishPig\WordPress\Helper\Date $dateHelper,
        \FishPig\WordPress\Model\Post\PasswordManager $passwordManager,
        \Magento\Customer\Model\Session $customerSession,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->postTypeRepository = $postTypeRepository;
        $this->postRepository = $postRepository;
        $this->termRepository = $termRepository;
        $this->taxonomyRepository = $taxonomyRepository;
        $this->userRepository = $userRepository;
        $this->shortcodeFactory = $shortcodeFactory;
        $this->termCollectionFactory = $termCollectionFactory;
        $this->imageFactory = $imageFactory;
        $this->dateHelper = $dateHelper;
        $this->frontPage = $frontPage;
        $this->passwordManager = $passwordManager;
        $this->customerSession = $customerSession;
        $this->directiveFilter = $wpContext->getDirectiveFilter();

        parent::__construct($context, $registry, $wpContext, $metaDataProvider, $resource, $resourceCollection, $data);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_getData('post_title');
    }

    /**
     * @param  string $type
     * @return bool
     */
    public function isType(string $type): bool
    {
        return $this->getPostType() === $type;
    }

    /**
     * @return \FishPig\WordPress\Model\PostType
     */
    public function getTypeInstance(): \FishPig\WordPress\Model\PostType
    {
        if ($this->typeInstance !== null && $this->typeInstance->getPostType() === $this->getPostType()) {
            return $this->typeInstance;
        }

        $this->typeInstance = null;

        if ($this->getPostType() === 'revision') {
            if ($this->getParentPost()) {
                $this->typeInstance = $this->getParentPost()->getTypeInstance();
            }
        } else {
            $this->typeInstance = $this->postTypeRepository->get($this->getPostType());
        }

        return $this->typeInstance;
    }

    /**
     * @return self
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $this->getResource()->preparePosts([$this]);

        return $this;
    }

    /**
     * 
     */
    public function getResource(): ResourceModel\Post
    {
        return parent::getResource();
    }

    /**
     * 
     */
    public function getCollection(): ResourceModel\Post\Collection
    {
        return parent::getCollection();
    }

    /**
     * @return string
     */
    public function getGuid(): string
    {
        if ($this->getPostType() === 'page') {
            return $this->url->getUrl() . '?page_id=' . $this->getId();
        } elseif ($this->getPostType() === 'post') {
            return $this->url->getUrl() . '?p=' . $this->getId();
        }

        return $this->url->getUrl() . '?p=' . $this->getId() . '&post_type=' . $this->getPostType();
    }

    /**
     * Retrieve the post excerpt
     * If no excerpt, try to shorten the post_content field
     *
     * @return string
     */
    public function getExcerpt($maxWords = 0)
    {
        if ($excerpt = trim($this->getData('post_excerpt'))) {
            return $this->directiveFilter->filter($this->getData('post_excerpt'), DirectiveFilter::SOURCE_CONTENT);
        }

        if ($excerpt = $this->_getPostTeaser(true)) {
            $this->setData('post_excerpt', $excerpt);

            return $this->directiveFilter->filter($excerpt, DirectiveFilter::SOURCE_CONTENT);
        }

        if ((int)$maxWords > 1) {
            $excerpt = trim(strip_tags(str_replace(["\n", '  ', '  '], ' ', $this->_getData('post_content'))));
            $excerpt = preg_replace('/\[[\/]{0,1}[^\]]{1,}\]/', '', $excerpt);
            $excerpt = preg_replace('/[\s]{1,}/', " ", $excerpt);
            $excerpt = explode(' ', $excerpt);

            if (count($excerpt) > $maxWords) {
                $excerpt = rtrim(
                    implode(' ', array_slice($excerpt, 0, $maxWords)),
                    "!@£$%^&*()_-+=[{]};:'\",<.>/? "
                ) . '...';
            } else {
                $excerpt = implode(' ', $excerpt);
            }

            return $excerpt;
        }

        return $this->getContent('excerpt');
    }

    /**
     * @deprecated use self::getExcerpt($maxWords)
     */
    public function getPostExcerpt($maxWords = 0)
    {
        return $this->getExcerpt($maxWords);
    }

    /**
     * Determine whether the post has a more tag in its content field
     *
     * @return bool
     */
    public function hasMoreTag()
    {
        return strpos($this->getData('post_content'), '<!--more') !== false;
    }

    /**
     * Retrieve the post teaser
     * This is the data from the post_content field upto to the MORE_TAG
     *
     * @return string
     */
    protected function _getPostTeaser($includeSuffix = true)
    {
        if ($this->hasMoreTag()) {
            $content = $this->getData('post_content');

            if (preg_match('/<!--more (.*)-->/', $content, $matches)) {
                $anchor = $matches[1];
                $split = $matches[0];
            } else {
                $split = '<!--more-->';
                $anchor = $this->_getTeaserAnchor();
            }

            $excerpt = (trim(substr($content, 0, strpos($content, $split))));

            if ($excerpt !== '' && $includeSuffix && $anchor) {
                $excerpt .= sprintf(' <a href="%s" class="read-more">%s</a>', $this->getUrl(), $anchor);
            }

            $excerpt = strip_tags(
                $this->shortcodeFactory->create()->setShortcode($excerpt)->setPost($this)->toHtml(),
                '<a><img><strong>'
            );

            return $excerpt;
        }

        return null;
    }

    /**
     * Retrieve the read more anchor text
     *
     * @return string|false
     */
    protected function _getTeaserAnchor()
    {
        // phpcs:ignore -- allows translation
        return stripslashes((string)__('Continue reading <span class=\"meta-nav\">&rarr;</span>'));
    }

    /**
     * @param  string $taxonomy
     * @return \FishPig\WordPress\Model\Term
     */
    public function getParentTerm($taxonomy)
    {
        if ($this->getSupportedTaxonomy($taxonomy)) {
            if ($termId = $this->getResource()->getParentTermId((int)$this->getId(), $taxonomy)) {
                return $this->termRepository->get($termId);
            }
        }

        return false;
    }

    /**
     * Get a collection of terms by the taxonomy
     *
     * @param  string $taxonomy
     * @return \FishPig\WordPress\Model\ResourceModel\Term\Collection
     */
    public function getTermCollection($taxonomy)
    {
        return $this->termCollectionFactory->create()
            ->addTaxonomyFilter(
                $taxonomy
            )->addPostIdFilter(
                $this->getId()
            );
    }

    /**
     * @param  ?string $taxonomy = null
     * @return \FishPig\WordPress\Model\Taxonomy|false
     */
    public function getSupportedTaxonomy(?string $taxonomy = null)
    {
        if ($supportedTaxonomies = $this->getTypeInstance()->getData('taxonomies')) {
            if ($taxonomy !== null) {
                if (in_array($taxonomy, $supportedTaxonomies)) {
                    return $this->taxonomyRepository->get($taxonomy);
                }
            } elseif ($taxonomy = array_shift($supportedTaxonomies)) {
                return $this->taxonomyRepository->get($taxonomy);
            }
        }

        return false;
    }

    /**
     *
     */
    public function getTermCollectionAsString($taxonomy, $joiner = ', ', $lastJoiner = ' &amp; ')
    {
        $key = 'term_collection_as_string_' . $taxonomy;

        if (!$this->hasData($key)) {
            $string = [];
            $terms = $this->getTermCollection($taxonomy);

            foreach ($terms as $term) {
                $string[] = sprintf('<a href="%s">%s</a>', $term->getUrl(), $term->getName());
            }

            $itemCount = count($string);

            if ($itemCount === 0) {
                $this->setData($key, false);
            } elseif ($itemCount === 1) {
                $this->setData($key, $string[0]);
            } else {
                $lastItem = array_pop($string);

                $this->setData($key, implode($joiner, $string) . $lastJoiner . $lastItem);
            }
        }

        return $this->_getData($key);
    }

    /**
     * Retrieve the previous post
     *
     * @return false|\FishPig\WordPress\Model\Post
     */
    public function getPreviousPost()
    {
        if (!$this->hasPreviousPost()) {
            $this->setPreviousPost(false);

            if ($collection = $this->getCollection()) {
                $collection->addIsViewableFilter()
                    ->addPostTypeFilter($this->getPostType())
                    ->addPostDateFilter(['lt' => $this->_getData('post_date')])
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->setOrderByPostDate()
                    ->load();

                if ($collection->count() > 0) {
                    $this->setPreviousPost($collection->getFirstItem());
                }
            }
        }

        return $this->_getData('previous_post');
    }

    /**
     * Retrieve the next post
     *
     * @return false|\FishPig\WordPress\Model\Post
     */
    public function getNextPost()
    {
        if (!$this->hasNextPost()) {
            $this->setNextPost(false);

            $collection = $this->getCollection()
                ->addIsViewableFilter()
                ->addPostTypeFilter($this->getPostType())
                ->addPostDateFilter(['gt' => $this->_getData('post_date')])
                ->setPageSize(1)
                ->setCurPage(1)
                ->setOrderByPostDate('asc')
                ->load();

            if ($collection->count() > 0) {
                $this->setNextPost($collection->getFirstItem());
            }
        }

        return $this->_getData('next_post');
    }

    /**
     * Retrieve the URL for the comments feed
     *
     * @return string
     */
    public function getCommentFeedUrl()
    {
        return rtrim($this->getUrl(), '/') . '/feed/';
    }

    /**
     * Wrapper for self::getContent
     *
     * @return string
     */
    public function getPostContent()
    {
        return $this->getContent();
    }

    /**
     * Gets the post content
     *
     * @return string
     */
    public function getContent()
    {
        $content = $this->getData('post_content');

        $canGetPreRenderedContent = strpos($content, '<!-- wp:') !== false
                                    || strpos($content, 'wp-block-embed') !== false
                                    || strpos($content, '<p') === false;

        if ($canGetPreRenderedContent) {
            if ($renderedContent = $this->getPreRenderedContent()) {
                if (strpos($renderedContent, '[') !== false) {
                    $renderedContent = $this->shortcodeFactory->create()
                        ->setShortcode($renderedContent)
                        ->setPost($this)
                        ->toHtml();
                }

               return $this->directiveFilter->filter($renderedContent, DirectiveFilter::SOURCE_CONTENT);
            }
        }

        $key = '__processed_post_content';

        if (!$this->hasData($key)) {
            $this->setData(
                $key,
                $this->directiveFilter->filter(
                    $this->shortcodeFactory->create()->setShortcode($content)->setPost($this)->toHtml(),
                    DirectiveFilter::SOURCE_CONTENT
                )
            );
        }

        return $this->getData($key);
    }

    /**
     * 
     */
    public function getPreRenderedContent(): ?string
    {
        return $this->getMetaValue(self::RENDERED_CONTENT_META_KEY) ?: null;
    }

    /**
     * 
     */
    public function getPreRenderedExerpt(): ?string
    {
        return $this->getMetaValue(self::RENDERED_EXERPT_META_KEY) ?: null;
    }

    /**
     * Returns a collection of comments for this post
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Comment\Collection
     */
    public function getComments()
    {
        if (!$this->hasData('comments')) {
            $this->setData('comments', $this->getResource()->getPostComments($this));
        }

        return $this->getData('comments');
    }

    /**
     * Returns a collection of images for this post
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Image\Collection
     *
     * NB. This function has not been thoroughly tested
     *        Please report any bugs
     */
    public function getImages()
    {
        if (!$this->hasData('images')) {
            $this->setImages(
                $this->imageFactory->create()->getCollection(
                )->setParent(
                    (int)$this->getData('ID')
                )
            );
        }

        return $this->getData('images');
    }

    /**
     * Returns the featured image for the post
     *
     * This image must be uploaded and assigned in the WP Admin
     *
     * @return \FishPig\WordPress\Model\Image
     */
    public function getImage()
    {
        if ($imageId = (int)$this->getMetaValue('_thumbnail_id')) {
            $image = $this->imageFactory->create()->load($imageId);

            return $image->getId() ? $image : false;
        }

        return false;
    }

    /**
     * Get the featured image
     *
     * @return     \FishPig\WordPress\Model\Image
     * @deprecated 1.0.0.0
     * @replace    self::getImage()
     */
    public function getFeaturedImage()
    {
        return $this->getImage();
    }

    /**
     * Get the model for the author of this post
     *
     * @return \FishPig\WordPress\Model\User
     */
    public function getUser()
    {
        return $this->userRepository->get($this->getUserId());
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->getPostAuthor();
    }

    /**
     * Returns the post date formatted
     * If not format is supplied, the format specified in your Magento config will be used
     *
     * @return string
     */
    public function getPostDate($format = null)
    {
        if (($date = $this->getData('post_date_gmt')) === '0000-00-00 00:00:00' || $date === '') {
            $date = date('Y-m-d H:i:s');
        }

        return $this->dateHelper->formatDate($date, $format);
    }

    /**
     * Returns the post date formatted
     * If not format is supplied, the format specified in your Magento config will be used
     *
     * @return string
     */
    public function getPostModifiedDate($format = null)
    {
        if (($date = $this->getData('post_modified_gmt')) === '0000-00-00 00:00:00' || $date === '') {
            $date = date('Y-m-d H:i:s');
        }

        return $this->dateHelper->formatDate($date, $format);
    }

    /**
     * Returns the post time formatted
     * If not format is supplied, the format specified in your Magento config will be used
     *
     * @return string
     */
    public function getPostTime($format = null)
    {
        if (($date = $this->getData('post_date_gmt')) === '0000-00-00 00:00:00' || $date === '') {
            $date = date('Y-m-d H:i:s');
        }

        return $this->wpContext->getDateHelper()->formatDate($date, $format);
    }

    /**
     * Determine whether the post has been published
     *
     * @return bool
     */
    public function isPublished()
    {
        return $this->getPostStatus() == 'publish';
    }

    /**
     * Determine whether the post has been published
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->getPostStatus() == 'pending';
    }

    /**
     * Retrieve the preview URL
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        if ($this->isPending()) {
            return $this->url->getUrl('?p=' . $this->getId() . '&preview=1');
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isViewableForVisitor(): bool
    {
        return $this->passwordManager->isPostUnlocked($this);
    }

    /**
     * Determine whether the post is a sticky post
     * This only works if the post collection has been loaded with addStickyPostsToCollection
     *
     * @return bool
     */
    public function isSticky()
    {
        return $this->_getData('is_sticky');
    }

    /**
     * Determine whether a post object can be viewed
     *
     * @return string
     */
    public function canBeViewed()
    {
        return $this->isPublished()
               || ($this->getPostStatus() === 'private' && $this->customerSession->isLoggedIn());
    }

    /**
     * Wrapper for self::getPermalink()
     *
     * @return string
     */
    public function getUrl()
    {
        if (!$this->hasUrl()) {
            $this->setUrl($this->getGuid());

            if ($this->isFrontPage()) {
                $this->setUrl($this->url->getUrl());
            } elseif ($this->hasPermalink()) {
                $this->setUrl(
                    $this->url->getUrl(
                        $this->_urlEncode($this->_getData('permalink'))
                    )
                );
            } elseif ($this->getTypeInstance() && $this->getTypeInstance()->isHierarchical()) {
                if ($uris = $this->getTypeInstance()->getAllRoutes()) {
                    if (isset($uris[$this->getId()])) {
                        $this->setUrl($this->url->getUrl($uris[$this->getId()] . '/'));
                    }
                }
            }
        }

        return $this->_getData('url');
    }

    /**
     * Encode the URL, ignoring '/' character
     *
     * @param  string $url
     * @return string
     */
    protected function _urlEncode($url)
    {
        if (strpos($url, '/') !== false) {
            $parts = explode('/', $url);

            foreach ($parts as $key => $value) {
                $parts[$key] = urlencode($value);
            }

            return implode('/', $parts);
        }

        return urlencode($url);
    }

    /**
     * Get the parent ID of the post
     *
     * @return int
     */
    public function getParentId()
    {
        return (int)$this->_getData('post_parent');
    }

    /**
     * Retrieve the parent page
     *
     * @return false|\FishPig\WordPress\Model\Post
     */
    public function getParentPost()
    {
        if (!$this->hasParentPost()) {
            $this->setParentPost(false);

            if ($parentId = (int)$this->getParentId()) {
                try {
                    $this->setParentPost(
                        $this->postRepository->getWithType(
                            $parentId,
                            $this->getPostType() === 'revision' ? '*' : $this->getPostType()
                        )
                    );
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->setParentPost(false);
                }
            }
        }

        return $this->_getData('parent_post');
    }

    /**
     * Retrieve the page's children pages
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getChildrenPosts()
    {
        return $this->getCollection()->addPostParentIdFilter($this->getId());
    }

    /**
     * Determine whether children exist
     *
     * @return bool
     */
    public function hasChildrenPosts()
    {
        return $this->getResource()->hasChildrenPosts($this);
    }

    /**
     * The methods here are legacy methods that have been ported over from the old Page class
     * These are deprecated and will be removed shortly.
     */

    /**
     *
     */
    public function getMenuLabel()
    {
        return $this->getPostTitle();
    }

    /**
     *
     */
    public function getParentPage()
    {
        return $this->isType('page') ? $this->getParentPost() : false;
    }

    /**
     *
     */
    public function hasChildren()
    {
        return $this->hasChildrenPosts();
    }

    /**
     *
     */
    public function getChildren()
    {
        return $this->getChildrenPosts();
    }

    /**
     *
     * @return bool
     */
    public function isFrontPage(): bool
    {
        return $this->isType('page') && (int)$this->getId() === $this->frontPage->getFrontPageId();
    }

    /**
     *
     * @return bool
     */
    public function isPostsPage(): bool
    {
        return $this->isType('page') && (int)$this->getId() === $this->frontPage->getPostsPageId();
    }

    /**
     * Get the post format string (eg. video or aside)
     *
     * @return string
     */
    public function getPostFormat()
    {
        if (!$this->hasPostFormat()) {
            $this->setPostFormat('');

            $formats = $this->termCollectionFactory->create()
                ->addTaxonomyFilter(
                    'post_format'
                )->setPageSize(
                    1
                )->addObjectIdFilter(
                    $this->getId()
                )->load();

            if (count($formats) > 0) {
                $this->setPostFormat(
                    str_replace('post-format-', '', $formats->getFirstItem()->getSlug())
                );
            }
        }

        return $this->_getData('post_format');
    }

    /**
     * Get the latest revision of the post
     *
     * @return FishPig\WordPress\Model\Post
     */
    public function getLatestRevision()
    {
        if (!$this->hasLatestRevision()) {
            $revision = $this->getCollection()
                ->addFieldToFilter('post_parent', $this->getId())
                ->addPostTypeFilter('revision')
                ->setPageSize(1)
                ->load()
                ->getFirstItem();

            $this->setLatestRevision($revision->getId() ? $revision : false);
        }

        return $this->_getData('latest_revision');
    }

    /**
     * @deprecated since 3.0
     */
    public function setAsGlobal(): self
    {
        return $this;
    }

    /**
     *
     */
    public function isPublic(): bool
    {
        if ($postType = $this->getTypeInstance()) {
            if (!$postType->isPublic()) {
                return false;
            }
        }

        return true;
    }
}
