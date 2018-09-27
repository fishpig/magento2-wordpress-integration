<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Adminhtml\Product;

use FishPig\WordPress\Block\Adminhtml\Catalog\Product\Edit\Tab\PostAssociation;
use Magento\Backend\App\Action;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;

class AssociationGrid extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'FishPig_WordPress::post_association';

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * Association constructor
     *
     * @param Action\Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Action\Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        ProductRepositoryInterface $productRepository,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('id')) {
            $this->initProduct();
        }
        /** @var Raw $rawResult */
        $rawResult = $this->resultRawFactory->create();
        return $rawResult->setContents(
            $this->layoutFactory->create()->createBlock(
                PostAssociation::class,
                'product.posts.grid'
            )->toHtml()
        );
    }

    /**
     * Initialise Product and Register to Registry
     */
    private function initProduct()
    {
        $id = $this->getRequest()->getParam('id');
        $product = $this->productRepository->getById($id);
        if ($this->registry->registry('product') != $product) {
            $this->registry->register('product', $product);
        }
    }
}
