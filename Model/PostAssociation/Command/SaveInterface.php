<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\PostAssociation\Command;

use FishPig\WordPress\Api\Data\PostAssociationInterface;

/**
 * Save Post Association command (Service Provider Interface - SPI)
 *
 * @api
 */
interface SaveInterface
{
    /**
     * Save Post Association
     *
     * @param PostAssociationInterface $postAssociation
     * @return \FishPig\WordPress\Api\Data\PostAssociationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(PostAssociationInterface $postAssociation): PostAssociationInterface;
}
