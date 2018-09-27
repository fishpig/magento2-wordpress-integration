<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use FishPig\WordPress\Api\Data\PostAssociationInterface;
use FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterface;

/**
 * Post Association Repository domain manager for PostAssociationInterface
 *
 * @api
 */
interface PostAssociationRepositoryInterface
{
    /**
     * Delete Post Association
     *
     * @param int $id
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(int $id);

    /**
     * Get Post Association By ID
     *
     * @param int $id
     * @return \FishPig\WordPress\Api\Data\PostAssociationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $id): PostAssociationInterface;

    /**
     * Get Post Association List by optional Search Criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): PostAssociationSearchResultsInterface;

    /**
     * Save Post Association
     *
     * @param \FishPig\WordPress\Api\Data\PostAssociationInterface $postAssociation
     * @return \FishPig\WordPress\Api\Data\PostAssociationInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(PostAssociationInterface $postAssociation): PostAssociationInterface;

    /**
     * Save Multiple Post Associations
     *
     * @param \FishPig\WordPress\Api\Data\PostAssociationInterface[] $postAssociations
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveMultiple(array $postAssociations);
}