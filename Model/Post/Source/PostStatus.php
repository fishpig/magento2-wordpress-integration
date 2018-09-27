<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Post\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class PostStatus extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**#@+
     * Blog Post Statuses
     */
    const AUTO_DRAFT = 'auto-draft';
    const DRAFT = 'draft';
    const PUBLISHED = 'publish';
    /**#@-*/

    /**
     * @inheritdoc
     */
    public static function getOptionArray(): array
    {
        return [
            self::AUTO_DRAFT => __('Auto Draft'),
            self::DRAFT => __('Draft'),
            self::PUBLISHED => __('Published')
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions(): array
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = [
                'value' => $index,
                'label' => $value
            ];
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ?
            $options[$optionId]:
            null;
    }
}
