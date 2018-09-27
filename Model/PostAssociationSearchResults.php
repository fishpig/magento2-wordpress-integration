<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class PostAssociationSearchResults extends SearchResults implements PostAssociationSearchResultsInterface
{
}
