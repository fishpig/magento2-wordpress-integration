<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Helper;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class PostAssociation extends AbstractHelper
{
    /**
     * @var UrlInterface
     */
    private $backendUrlBuilder;

    /**
     * Date constructor
     *
     * @param Context $context
     * @param UrlInterface $backendUrlBuilder
     */
    public function __construct(
        Context $context,
        UrlInterface $backendUrlBuilder
    ) {
        parent::__construct($context);
        $this->backendUrlBuilder = $backendUrlBuilder;
    }

    /**
     * Return URL for Product Post Association Tab in Admin
     *
     * @return string
     */
    public function getProductAssociationTabUrl(): string
    {
        return $this->backendUrlBuilder->getUrl(
            'posts/product/association',
            ['current' => true]
        );
    }
}
