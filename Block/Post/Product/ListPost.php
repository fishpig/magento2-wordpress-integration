<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Post\Product;

use FishPig\WordPress\Model\Context as WPContext;
use FishPig\WordPress\Model\ResourceModel\Post\Collection;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Framework\View\Element\Template\Context;

class ListPost extends \FishPig\WordPress\Block\Post\ListPost
{
    /**
     * @inheritdoc
     */
    public function getPosts()
    {
        if ($this->_postCollection === null) {
            if ($product = $this->getProduct()) {
                $postIds = $this->getProductsAssociatedPostIds(
                    $product
                );
                if (!empty($postIds)) {
                    $this->_postCollection = $this->factory->create(
                        'FishPig\WordPress\Model\ResourceModel\Post\Collection'
                    );
                    $this->_postCollection
                        ->addFieldToFilter('ID', ['in' => $postIds]);
                }
            }
        }
        return $this->_postCollection;
    }

    /**
     * Return array of Blog Post IDs
     *
     * @param $product
     * @return array
     */
    public function getProductsAssociatedPostIds($product): array
    {
        $postIds = [];
        /** @var ProductExtension $extensionAttributes */
        if ($extensionAttributes = $product->getExtensionAttributes()) {
            $postAssociations = $extensionAttributes->getPostAssociations();
            if (!empty($postAssociations)) {
                $postIds = array_map(
                    [$this, 'getPostIds'],
                    $postAssociations
                );
            }
            return $postIds;
        }
        return $postIds;
    }

    /**
     * Return Post ID from Post Association
     *
     * @param $postAssociationItem
     * @return int
     */
    private function getPostIds($postAssociationItem): int
    {
        return $postAssociationItem->getPostId();
    }

    /**
     * Return Product from Registry
     *
     * @return mixed
     */
    private function getProduct()
    {
        return $this->wpContext->getRegistry()->registry('product');
    }
}
