<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\PostAssociation\Command;

use FishPig\WordPress\Api\Data\PostAssociationInterface;

/**
 * Get Post Association by ID command (Service Provider Interface - SPI)
 *
 * @api
 */
interface GetInterface
{
    /**
     * Get Post Association By ID
     *
     * @param int $id
     * @return \FishPig\WordPress\Api\Data\PostAssociationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(int $id): PostAssociationInterface;
}
