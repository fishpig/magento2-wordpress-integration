<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Observer\Adminhtml;

use FishPig\WordPress\Api\Data\PostAssociationInterfaceFactory;
use FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterface;
use FishPig\WordPress\Api\PostAssociationRepositoryInterface;
use FishPig\WordPress\Model\ResourceModel\PostAssociation\CollectionFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogProductSave implements ObserverInterface
{
    /**
     * @var PostAssociationRepositoryInterface
     */
    private $postAssociationRepository;

    /**
     * @var PostAssociationInterfaceFactory
     */
    private $postAssociationFactory;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * Array of Post IDs to associate to product
     *
     * @var array
     */
    private $postIds;

    /**
     * CatalogProductSave constructor
     *
     * @param PostAssociationInterfaceFactory $postAssociationFactory
     * @param PostAssociationRepositoryInterface $postAssociationRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        PostAssociationInterfaceFactory $postAssociationFactory,
        PostAssociationRepositoryInterface $postAssociationRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->postAssociationRepository = $postAssociationRepository;
        $this->postAssociationFactory = $postAssociationFactory;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Handle Admin Product Save Associated Blog Posts
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();
        /** @var Save $controller */
        $controller = $observer->getEvent()->getController();
        $productSavePostData = $controller->getRequest()->getPostValue();
        if (isset($productSavePostData['product_posts'])
            && is_string($productSavePostData['product_posts'])
        ) {
            $posts = json_decode($productSavePostData['product_posts'], true);
            if (!empty($posts)) {
                $this->postIds = array_keys($posts);
                $existentAssociatedPostIds = $this->getExistentPostIds(
                    (int)$product->getId()
                );
                $postAssociationIdsToDelete = array_filter(
                    $existentAssociatedPostIds,
                    [$this, 'isInPostIdsToSave']
                );
                if (!empty($postAssociationIdsToDelete)) {
                    $this->deletePostAssociations(array_keys($postAssociationIdsToDelete));
                }
                $newPostAssociations = [];
                foreach ($this->postIds as $postId) {
                    if (!in_array($postId, $existentAssociatedPostIds)) {
                        $postAssociation = $this->postAssociationFactory->create();
                        $postAssociation->setProductId((int)$product->getId());
                        $postAssociation->setPostId((int)$postId);
                        $newPostAssociations[] = $postAssociation;
                    }
                }
                if (!empty($newPostAssociations)) {
                    $this->postAssociationRepository->saveMultiple($newPostAssociations);
                }
            }
        }
    }

    /**
     * Returns bool on whether post id is in ids to be saved (true if not in array)
     *
     * @param $postId
     * @return bool
     */
    private function isInPostIdsToSave($postId): bool
    {
        return (!in_array($postId, $this->postIds));
    }

    /**
     * Returns bool on whether post id is in ids to be saved (true if not in array)
     *
     * @param $postId
     * @return void
     */
    private function deletePostAssociations($associationIdsToDelete)
    {
        foreach ($associationIdsToDelete as $associationIdToDelete) {
            $this->postAssociationRepository->delete($associationIdToDelete);
        }
    }

    /**
     * Return array of previously associated Post Ids
     *
     * @param int $productId
     * @return array
     */
    private function getExistentPostIds(int $productId): array
    {
        $associatedPostIds = [];
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $searchCriteriaBuilder->addFilter(
            'product_id',
            $productId
        )->create();
        /** @var PostAssociationSearchResultsInterface $postAssociations */
        $postAssociations = $this->postAssociationRepository->getList($searchCriteria);
        if ($postAssociations->getTotalCount() > 0) {
            foreach ($postAssociations->getItems() as $postAssociation) {
                $associatedPostIds[$postAssociation->getId()] = $postAssociation->getPostId();
            }
        }
        return $associatedPostIds;
    }
}
