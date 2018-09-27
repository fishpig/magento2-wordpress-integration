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
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Save implements SaveInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Save constructor
     *
     * @param PostAssociationInterfaceFactory $postAssociationFactory
     * @param PostAssociationResource $postAssociationResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        PostAssociationInterfaceFactory $postAssociationFactory,
        PostAssociationResource $postAssociationResource,
        LoggerInterface $logger
    ) {
        $this->postAssociationFactory = $postAssociationFactory;
        $this->postAssociationResource = $postAssociationResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(PostAssociationInterface $postAssociation): PostAssociationInterface
    {
        try {
            if (!$postAssociation->getId()) {
                /** @var PostAssociationInterface $existentSubscriber */
                $existentSubscriber = $this->postAssociationFactory->create();
                $this->postAssociationResource->load(
                    $existentSubscriber,
                    $postAssociation->getEmail(),
                    PostAssociationInterface::EMAIL
                );
                if (null !== $existentSubscriber->getId()) {
                    $postAssociation->setId($existentSubscriber->getId());
                }
            }
            $this->postAssociationResource->save($postAssociation);
            $postAssociation->getId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could Not Save Post Association', $e));
        }
        return $postAssociation;
    }
}
