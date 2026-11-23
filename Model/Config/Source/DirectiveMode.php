<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Config\Source;

use FishPig\WordPress\App\View\DirectiveFilter;

class DirectiveMode implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => DirectiveFilter::MODE_ALL, 'label' => __('All WordPress Content')],
            ['value' => DirectiveFilter::MODE_SPECIFIC, 'label' => __('Specific Content')],
        ];
    }
}
