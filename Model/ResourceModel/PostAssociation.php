<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel;

use FishPig\WordPress\Api\Data\PostAssociationInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Post Association Resource
 */
class PostAssociation extends AbstractDb
{
    /**#@+
     * Main Table Name Constant
     */
    const POST_ASSOCIATION_TABLE_NAME = 'catalog_product_post_association';
    /**#@-*/

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(
            self::POST_ASSOCIATION_TABLE_NAME,
            PostAssociationInterface::ID
        );
    }
}
