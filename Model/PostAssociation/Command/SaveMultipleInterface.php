<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\PostAssociation\Command;

use FishPig\WordPress\Api\Data\PostAssociationInterface;

/**
 * Save Multiple Post Associations command (Service Provider Interface - SPI)
 *
 * @api
 */
interface SaveMultipleInterface
{
    /**
     * Save Multiple Post Associations
     *
     * @param PostAssociationInterface[] $postAssociation
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(array $postAssociation);
}
