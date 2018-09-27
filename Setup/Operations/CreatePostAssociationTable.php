<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Setup\Operations;

use FishPig\WordPress\Api\Data\PostAssociationInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Operational Class to create Post Association Table
 */
class CreatePostAssociationTable
{
    /**
     * Post Association Table Name
     */
    const CATALOG_PRODUCT_POST_ASSOCIATION_TABLE_NAME = 'catalog_product_post_association';

    /**
     * Execute table creation logic
     *
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $postAssociationTable = $setup->getConnection()->newTable(
            $setup->getTable(
                self::CATALOG_PRODUCT_POST_ASSOCIATION_TABLE_NAME
            )
        )->setComment(
            'Product Blog Post Association Table'
        );
        $postAssociationTable = $this->addColumns($postAssociationTable);
        $postAssociationTable = $this->addRelations(
            $postAssociationTable,
            $setup
        );
        $setup->getConnection()->createTable($postAssociationTable);
        $setup->endSetup();
    }

    /**
     * Add columns to table to match data key constants
     * 
     * @param Table $postAssociationTable
     * @return Table
     */
    private function addColumns(Table $postAssociationTable): Table
    {
        return $postAssociationTable->addColumn(
            PostAssociationInterface::ID,
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Post Association Entity ID'
        )->addColumn(
            PostAssociationInterface::PRODUCT_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Product Id'
        )->addColumn(
            PostAssociationInterface::POST_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Post Id'
        );
    }

    /**
     * Add Table Relations, Foreign Keys, Indexes etc.
     *
     * @param Table $postAssociationTable
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function addRelations(
        Table $postAssociationTable,
        SchemaSetupInterface $setup
    ): Table {
        return $postAssociationTable->addForeignKey(
            $setup->getFkName(
                self::CATALOG_PRODUCT_POST_ASSOCIATION_TABLE_NAME,
                PostAssociationInterface::PRODUCT_ID,
                'catalog_product_entity',
                'entity_id'
            ),
            PostAssociationInterface::PRODUCT_ID,
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );
    }
}
