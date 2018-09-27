<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\PostAssociation\Command;

/**
 * Command class for Deleting Post Associations
 *
 * @api
 */
interface DeleteInterface
{
    /**
     * Delete Post Association by ID
     *
     * @param int $id
     * @return void
     */
    public function execute(int $id);
}
