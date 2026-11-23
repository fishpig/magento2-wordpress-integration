<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Post\View;

class SeoMetaDataProvider extends \FishPig\WordPress\Controller\Action\SeoMetaDataProvider
{
    /**
     * @param  \Magento\Framework\View\Result\Page $resultPage,
     * @param  \FishPig\WordPress\Api\Data\ViewableModelInterface $object
     * @return void
     */
    public function addMetaData(
        \Magento\Framework\View\Result\Page $resultPage,
        \FishPig\WordPress\Api\Data\ViewableModelInterface $post
    ): void {
        parent::addMetaData($resultPage, $post);
        
        $this->setMetaTitleWithBlogName($post->getName());
        $this->setCanonicalUrl($post->getUrl());
        $this->setPageTitle($post->getName());

        // Don't leak the excerpt/content of a password protected post
        if (!$post->isViewableForVisitor()) {
            // Leave the blog description set by the parent
        } elseif ($description = $post->getPostExcerpt(32)) {
            $this->setMetaDescription($description);
        } elseif ($description = strip_tags($post->getContent())) {
            if (strlen($description) > 250) {
                $description = rtrim(substr($description, 0, 250));
            }
            $this->setMetaDescription($description);
        }

        if (!$post->isPublic()) {
            $this->setRobots('NOINDEX,NOFOLLOW');
        }
    }
}
