<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\PostAssociation;

use Magento\Framework\App\ResourceConnection;
use FishPig\WordPress\Model\ResourceModel\PostAssociation as PostAssociationResource;
use FishPig\WordPress\Api\Data\PostAssociationInterface;

class SaveMultiple
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * SaveMultiple constructor
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Save Multiple Post Associations
     *
     * @param PostAssociationInterface[] $postAssociations
     * @return void
     */
    public function execute(array $postAssociations)
    {
        if (!count($postAssociations)) {
            return;
        }
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(
            PostAssociationResource::POST_ASSOCIATION_TABLE_NAME
        );
        $columnsSql = $this->buildColumnsSqlPart([
            PostAssociationInterface::PRODUCT_ID,
            PostAssociationInterface::POST_ID
        ]);
        $valuesSql = $this->buildValuesSqlPart($postAssociations);
        $onDuplicateSql = $this->buildOnDuplicateSqlPart([
            PostAssociationInterface::PRODUCT_ID,
            PostAssociationInterface::POST_ID
        ]);
        $bind = $this->getSqlBindData($postAssociations);
        $insertSql = sprintf(
            'INSERT INTO %s (%s) VALUES %s %s',
            $tableName,
            $columnsSql,
            $valuesSql,
            $onDuplicateSql
        );
        $connection->query($insertSql, $bind);
    }

    /**
     * @param array $columns
     * @return string
     */
    private function buildColumnsSqlPart(array $columns): string
    {
        $connection = $this->resourceConnection->getConnection();
        $processedColumns = array_map([$connection, 'quoteIdentifier'], $columns);
        $sql = implode(', ', $processedColumns);
        return $sql;
    }

    /**
     * @param PostAssociationInterface[] $postAssociations
     * @return string
     */
    private function buildValuesSqlPart(array $postAssociations): string
    {
        $sql = rtrim(str_repeat('(?, ?), ', count($postAssociations)), ', ');
        return $sql;
    }

    /**
     * @param PostAssociationInterface[] $postAssociations
     * @return array
     */
    private function getSqlBindData(array $postAssociations): array
    {
        $bind = [];
        foreach ($postAssociations as $postAssociation) {
            $bind = array_merge($bind, [
                $postAssociation->getProductId(),
                $postAssociation->getPostId()
            ]);
        }
        return $bind;
    }

    /**
     * @param array $fields
     * @return string
     */
    private function buildOnDuplicateSqlPart(array $fields): string
    {
        $connection = $this->resourceConnection->getConnection();
        $processedFields = [];
        foreach ($fields as $field) {
            $processedFields[] = sprintf('%1$s = VALUES(%1$s)', $connection->quoteIdentifier($field));
        }
        $sql = 'ON DUPLICATE KEY UPDATE ' . implode(', ', $processedFields);
        return $sql;
    }

}
