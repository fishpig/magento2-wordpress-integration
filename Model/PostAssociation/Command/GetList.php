<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\PostAssociation\Command;

use FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterface;
use FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterfaceFactory;
use FishPig\WordPress\Model\ResourceModel\PostAssociation\Collection;
use FishPig\WordPress\Model\ResourceModel\PostAssociation\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;

class GetList implements GetListInterface
{
    /**
     * @var PostAssociationSearchResultsInterfaceFactory
     */
    private $postAssociationSearchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * GetList constructor
     *
     * @param PostAssociationSearchResultsInterfaceFactory $postAssociationSearchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        PostAssociationSearchResultsInterfaceFactory $postAssociationSearchResultsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->postAssociationSearchResultsFactory = $postAssociationSearchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function execute(SearchCriteriaInterface $searchCriteria = null): PostAssociationSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        if (null === $searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        } else {
            $this->collectionProcessor->process($searchCriteria, $collection);
        }
        /** @var PostAssociationSearchResultsInterface $searchResults */
        $searchResults = $this->postAssociationSearchResultsFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setSearchCriteria($searchCriteria);
        return $searchResults;
    }
}
