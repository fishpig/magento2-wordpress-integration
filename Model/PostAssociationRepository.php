<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\PostAssociationInterface;
use FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterface;
use FishPig\WordPress\Api\PostAssociationRepositoryInterface;
use FishPig\WordPress\Model\PostAssociation\Command\DeleteInterface;
use FishPig\WordPress\Model\PostAssociation\Command\GetByPostIdInterface;
use FishPig\WordPress\Model\PostAssociation\Command\GetByProductIdInterface;
use FishPig\WordPress\Model\PostAssociation\Command\GetInterface;
use FishPig\WordPress\Model\PostAssociation\Command\GetListInterface;
use FishPig\WordPress\Model\PostAssociation\Command\SaveInterface;
use FishPig\WordPress\Model\PostAssociation\Command\SaveMultipleInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @inheritdoc
 */
class PostAssociationRepository implements PostAssociationRepositoryInterface
{
    /**
     * @var DeleteInterface
     */
    private $deleteCommand;

    /**
     * @var GetInterface
     */
    private $getCommand;

    /**
     * @var GetListInterface
     */
    private $getListCommand;

    /**
     * @var SaveInterface
     */
    private $saveCommand;

    /**
     * @var SaveMultipleInterface
     */
    private $saveMultipleCommand;

    /**
     * PostAssociationRepository constructor
     *
     * @param DeleteInterface $delete
     * @param GetInterface $get
     * @param GetListInterface $getList
     * @param SaveInterface $save
     * @param SaveMultipleInterface $saveMultiple
     */
    public function __construct(
        DeleteInterface $delete,
        GetInterface $get,
        GetListInterface $getList,
        SaveInterface $save,
        SaveMultipleInterface $saveMultiple
    ) {
        $this->deleteCommand = $delete;
        $this->getCommand = $get;
        $this->getListCommand = $getList;
        $this->saveCommand = $save;
        $this->saveMultipleCommand = $saveMultiple;
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        $this->deleteCommand->execute($id);
    }

    /**
     * @inheritdoc
     */
    public function get(int $id): PostAssociationInterface
    {
        return $this->getCommand->execute($id);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): PostAssociationSearchResultsInterface
    {
        return $this->getListCommand->execute($searchCriteria);
    }

    /**
     * @inheritdoc
     */
    public function save(PostAssociationInterface $postAssociation): PostAssociationInterface
    {
        return $this->saveCommand->execute($postAssociation);
    }

    /**
     * @inheritdoc
     */
    public function saveMultiple(array $postAssociations)
    {
        return $this->saveMultipleCommand->execute($postAssociations);
    }
}
