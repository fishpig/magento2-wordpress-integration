<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\PostAssociationExtensionInterface;
use FishPig\WordPress\Api\Data\PostAssociationInterface;
use FishPig\WordPress\Model\ResourceModel\PostAssociation as PostAssociationResource;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * @inheritdoc
 */
class PostAssociation extends AbstractExtensibleModel implements PostAssociationInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(PostAssociationResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::ID) === null ?
            null:
            (int)$this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($subscriberId)
    {
        $this->setData(self::ID, $subscriberId);
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID) === null ?
            null:
            (int)$this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setProductId(int $productId)
    {
        $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritdoc
     */
    public function getPostId()
    {
        return $this->getData(self::POST_ID) === null ?
            null:
            (int)$this->getData(self::POST_ID);
    }

    /**
     * @inheritdoc
     */
    public function setPostId(int $postId)
    {
        $this->setData(self::POST_ID, $postId);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): PostAssociationExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory
                ->create(PostAssociationInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(PostAssociationExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
