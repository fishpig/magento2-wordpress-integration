<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\PostAssociation\Command;

use FishPig\WordPress\Api\Data\PostAssociationInterface;
use FishPig\WordPress\Api\Data\PostAssociationInterfaceFactory;
use FishPig\WordPress\Model\ResourceModel\PostAssociation as PostAssociationResource;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete implements DeleteInterface
{
    /**
     * @var PostAssociationInterfaceFactory
     */
    private $postAssociationFactory;

    /**
     * @var PostAssociationResource
     */
    private $postAssociationResource;

    /**
     * Delete constructor
     *
     * @param PostAssociationInterfaceFactory $postAssociationFactory
     * @param PostAssociationResource $postAssociationResource
     */
    public function __construct(
        PostAssociationInterfaceFactory $postAssociationFactory,
        PostAssociationResource $postAssociationResource
    ) {
        $this->postAssociationFactory = $postAssociationFactory;
        $this->postAssociationResource = $postAssociationResource;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $id)
    {
        if (null === $id) {
            return;
        }
        try {
            /** @var PostAssociationInterface $postAssociation */
            $postAssociation = $this->postAssociationFactory->create();
            $this->postAssociationResource->load(
                $postAssociation,
                $id,
                PostAssociationInterface::ID
            );
            $this->postAssociationResource->delete($postAssociation);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could Not Delete Post Association', $e));
        }
    }
}
