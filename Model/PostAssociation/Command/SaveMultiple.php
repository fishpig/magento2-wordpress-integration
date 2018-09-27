<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\PostAssociation\Command;

use FishPig\WordPress\Api\Data\PostAssociationInterface;
use FishPig\WordPress\Model\ResourceModel\PostAssociation\SaveMultiple as SaveMultipleResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

/**
 * @inheritdoc
 */
class SaveMultiple implements SaveMultipleInterface
{
    /**
     * @var SaveMultipleResource
     */
    private $saveMultipleResource;

    /**
     * SaveMultiple constructor
     *
     * @param SaveMultipleResource $saveMultipleResource
     */
    public function __construct(
        SaveMultipleResource $saveMultipleResource
    ) {
        $this->saveMultipleResource = $saveMultipleResource;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $postAssociations)
    {
        if (empty($postAssociations)) {
            throw new InputException(__('Input data is empty'));
        }
        try {
            $this->saveMultipleResource->execute($postAssociations);
        } catch (\Exception $e) {
            var_dump($e->getMessage());die;
            throw new CouldNotSaveException(
                __('Could not save Post Associations',
                    $e
                )
            );
        }
    }
}
