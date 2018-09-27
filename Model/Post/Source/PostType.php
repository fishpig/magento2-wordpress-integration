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

class PostType extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**#@+
     * Blog Post Status Contants
     */
    const POST = 'post';
    const PAGE = 'page';
    /**#@-*/

    /**
     * @inheritdoc
     */
    public static function getOptionArray(): array
    {
        return [
            self::POST => __('Post'),
            self::PAGE => __('Page')
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
