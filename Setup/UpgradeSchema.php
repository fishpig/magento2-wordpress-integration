<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Setup;

use FishPig\WordPress\Setup\Operations\CreatePostAssociationTable;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var CreatePostAssociationTable
     */
    private $createPostAssociationTableOperation;

    /**
     * UpgradeSchema constructor
     *
     * @param CreatePostAssociationTable $createPostAssociationTable
     */
    public function __construct(
        CreatePostAssociationTable $createPostAssociationTable
    ) {
        $this->createPostAssociationTableOperation = $createPostAssociationTable;
    }

    /**
     * Perform Module specific Schema Upgrades
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '2.0.1.6') < 0) {
            $this->createPostAssociationTableOperation->execute($setup);
        }
    }
}
