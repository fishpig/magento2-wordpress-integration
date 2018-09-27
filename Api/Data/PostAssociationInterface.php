<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Api\Data;

use FishPig\WordPress\Api\Data\PostAssociationExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Post Association Data Interface provides a relation between Products and Blog Posts
 *
 * @api
 */
interface PostAssociationInterface extends ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const PRODUCT_ID = 'product_id';
    const POST_ID = 'post_id';

    /**
     * Get Association ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Association ID
     *
     * @param mixed $associationId
     * @return void
     */
    public function setId($associationId);

    /**
     * Get Association Product ID
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Set Association Product ID
     *
     * @param int $productId
     * @return void
     */
    public function setProductId(int $productId);

    /**
     * Get Association Post ID
     *
     * @return int|null
     */
    public function getPostId();

    /**
     * Set Association Post ID
     *
     * @param int $postId
     * @return void
     */
    public function setPostId(int $postId);
    
    /**
     * Get Extension Attirbutes
     *
     * @return \FishPig\WordPress\Api\Data\PostAssociationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set Extension Attributes
     *
     * @param \FishPig\WordPress\Api\Data\PostAssociationExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(PostAssociationExtensionInterface $extensionAttributes);
}