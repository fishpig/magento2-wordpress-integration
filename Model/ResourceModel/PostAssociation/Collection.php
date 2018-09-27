<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\PostAssociation;

use FishPig\WordPress\Model\PostAssociation;
use FishPig\WordPress\Model\ResourceModel\PostAssociation as PostAssociationResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Post Association Collection Resource
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected $_idFieldName = 'id';

    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init(
            PostAssociation::class,
            PostAssociationResource::class
        );
    }
}
