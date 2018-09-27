<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Plugin\Catalog;

use FishPig\WordPress\Api\Data\PostAssociationInterface;
use FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterface;
use FishPig\WordPress\Api\PostAssociationRepositoryInterface;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface as Subject;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class ProductRepository
{
    /**
     * @var ProductExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var PostAssociationRepositoryInterface
     */
    private $postAssociationRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * ProductRepository constructor
     *
     * @param ProductExtensionFactory $extensionFactory
     * @param PostAssociationRepositoryInterface $postAssociationRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        ProductExtensionFactory $extensionFactory,
        PostAssociationRepositoryInterface $postAssociationRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->postAssociationRepository = $postAssociationRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Set Associations via ExtensionAttributes
     *
     * @param Subject $subject
     * @param $sku
     * @param $result
     * @return mixed
     */
    public function afterGet(
        Subject $subject,
        $result,
        $sku
    ) {
        /** @var ProductExtension $extensionAttributes */
        $extensionAttributes = $result->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionFactory->create();
        }
        if (!$extensionAttributes->getPostAssociations()) {
            $assocations = $this->getProductPostAssociations((int)$result->getId());
            $extensionAttributes->setPostAssociations($assocations);
        }
        $result->setExtensionAttributes($extensionAttributes);
        return $result;
    }

    /**
     * Set Associations via ExtensionAttributes
     *
     * @param Subject $subject
     * @param $id
     * @param $result
     * @return mixed
     */
    public function afterGetById(
        Subject $subject,
        $result,
        $id
    ) {
        /** @var ProductExtension $extensionAttributes */
        $extensionAttributes = $result->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionFactory->create();
        }
        if (!$extensionAttributes->getPostAssociations()) {
            $assocations = $this->getProductPostAssociations((int)$result->getId());
            $extensionAttributes->setPostAssociations($assocations);
        }
        $result->setExtensionAttributes($extensionAttributes);
        return $result;
    }

    /**
     * Set Associations via ExtensionAttributes on getList result
     *
     * @param Subject $subject
     * @param $searchCriteria
     * @param $result
     * @return mixed
     */
    public function afterGetList(
        Subject $subject,
        $result,
        $searchCriteria
    ) {
       if ($result->getTotalCount() > 0) {
           foreach ($result->getItems() as $product) {
               /** @var ProductExtension $extensionAttributes */
               $extensionAttributes = $product->getExtensionAttributes();
               if (null === $extensionAttributes) {
                   $extensionAttributes = $this->extensionFactory->create();
               }
               if (!$extensionAttributes->getPostAssociations()) {
                   $assocations = $this->getProductPostAssociations((int)$product->getId());
                   $extensionAttributes->setPostAssociations($assocations);
               }
               $product->setExtensionAttributes($extensionAttributes);
           }
       }
       return $result;
    }

    /**
     * Handle saving of product assocaitions from product extension attributes passed via API
     *
     * @param Subject $subject
     * @param $productToSave
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function afterSave(
        Subject $subject,
        $result,
        $productToSave
    ) {
        $extensionAttributes = $productToSave->getExtensionAttributes();
        if ($extensionAttributes !== null) {
            /** @var ProductExtension $extensionAttributes */
            if ($postAssociations = $extensionAttributes->getPostAssociations()) {
                $postIdsToSave = array_map(
                    [$this, 'getIdsToSave'],
                    $postAssociations
                );
                $existingPostAssociationsPostIds = $this->getProductPostAssociationsPostIds(
                    (int)$result->getId()
                );
                $postAssociationsToDelete = $this->getPostAssociationsToDelete(
                    $postIdsToSave,
                    $existingPostAssociationsPostIds
                );
                if (!empty($postAssociationsToDelete)) {
                    $this->deletePostAssociations(array_keys($postAssociationsToDelete));
                }
                $postAssociationsToSave = [];
                /** @var PostAssociationInterface $postAssociation */
                foreach ($postAssociations as $postAssociation) {
                    if (!in_array($postAssociation->getPostId(), $existingPostAssociationsPostIds)) {
                        $postAssociationsToSave[] = $postAssociation;
                    }
                }
                if (!empty($postAssociationsToSave)) {
                    $this->postAssociationRepository->saveMultiple($postAssociationsToSave);
                }
                $extensionAttributes->setPostAssociations(
                    $this->getProductPostAssociations((int)$result->getId())
                );
                $result->setExtensionAttributes($extensionAttributes);
            }
        }
        return $result;
    }

    /**
     * Delete any given association post ids
     *
     * @param array $associatedPostIds
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function deletePostAssociations(array $associatedPostIds)
    {
        foreach ($associatedPostIds as $associatedPostId) {
            $this->postAssociationRepository->delete($associatedPostId);
        }
    }

    /**
     * Return Post Ids to delete
     *
     * @param $postIdsToSave
     * @param $existingAssociatedPostIds
     * @return array
     */
    private function getPostAssociationsToDelete(
        $postIdsToSave,
        $existingAssociatedPostIds
    ): array {
        $idsToDelete = [];
        foreach ($existingAssociatedPostIds as $assocationId => $postId) {
            if (!in_array($postId, $postIdsToSave)) {
                $idsToDelete[$assocationId] = $postId;
            }
        }
        return $idsToDelete;
    }

    /**
     * Return Post ID from PostAssocation Object
     *
     * @param PostAssociationInterface $postAssociation
     * @return mixed
     */
    private function getIdsToSave($postAssociation)
    {
        return $postAssociation->getPostId();
    }

    /**
     * Return Any Post Associations for given product ID
     *
     * @param int $productId
     * @return array
     */
    public function getProductPostAssociations(int $productId): array
    {
        $associatedPosts = [];
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        /** @var SearchCriteria $searcCriteria */
        $searcCriteria = $searchCriteriaBuilder->addFilter(
            'product_id',
            $productId
        )->create();
        /** @var PostAssociationSearchResultsInterface $searchResults */
        $searchResults = $this->postAssociationRepository->getList($searcCriteria);
        if ($searchResults->getTotalCount() > 0) {
            $associatedPosts = $searchResults->getItems();
        }
        return $associatedPosts;
    }

    /**
     * Return Any Post Associations for given product ID
     *
     * @param int $productId
     * @return array
     */
    public function getProductPostAssociationsPostIds(int $productId): array
    {
        $postIds = [];
        $associatedPosts = $this->getProductPostAssociations($productId);
        if (!empty($associatedPosts)) {
            foreach ($associatedPosts as $associatedPost) {
                $postIds[$associatedPost->getId()] = $associatedPost->getPostId();
            }
        }
        return $postIds;
    }
}
