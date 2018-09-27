<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Search results for PostAssociationRepositoryInterface::getList method
 *
 * @api
 */
interface PostAssociationSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get List of Associations
     *
     * @return \FishPig\WordPress\Api\Data\PostAssociationInterface[]
     */
    public function getItems();

    /**
     * Set List of Associations
     *
     * @param \FishPig\WordPress\Api\Data\PostAssociationInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
