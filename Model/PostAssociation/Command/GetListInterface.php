<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\PostAssociation\Command;

use FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Get Post Association List by optional Search Criteria command (Service Provider Interface - SPI)
 *
 * @api
 */
interface GetListInterface
{
    /**
     * Get Post Associations List by optional Search Criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria = null): PostAssociationSearchResultsInterface;
}
