<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Adminhtml\Catalog\Product\Edit;

use FishPig\WordPress\Api\Data\PostAssociationInterface;
use FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterface;
use FishPig\WordPress\Api\PostAssociationRepositoryInterface;
use Magento\Backend\Block\Template;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;

class PostAssociation extends Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'catalog/product/edit/associated-posts.phtml';

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var \FishPig\WordPress\Block\Adminhtml\Catalog\Product\Edit\Tab\PostAssociation
     */
    private $blockGrid;

    /**
     * @var PostAssociationRepositoryInterface
     */
    private $postAssociationRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * PostAssociation constructor
     * 
     * @param Template\Context $context
     * @param Registry $registry
     * @param EncoderInterface $jsonEncoder
     * @param PostAssociationRepositoryInterface $postAssociationRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        EncoderInterface $jsonEncoder,
        PostAssociationRepositoryInterface $postAssociationRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->postAssociationRepository = $postAssociationRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Retrieve instance of Grid Block
     *
     * @return Tab\PostAssociation|\Magento\Framework\View\Element\BlockInterface
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \FishPig\WordPress\Block\Adminhtml\Catalog\Product\Edit\Tab\PostAssociation::class,
                'product.posts.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml(): string
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getPostsJson(): string
    {
        $postIds = [];
        /** @var ProductInterface $product */
        $product = $this->getProduct();
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $searchCriteriaBuilder->addFilter(
            'product_id',
            $product->getId()
        )->create();
        /** @var PostAssociationSearchResultsInterface $postAssociations */
        $postAssociations = $this->postAssociationRepository->getList($searchCriteria);
        if ($postAssociations->getTotalCount() > 0) {
            /** @var PostAssociationInterface $postAssociation */
            foreach ($postAssociations->getItems() as $postAssociation) {
                $postIds[$postAssociation->getPostId()] = 1;
            }
        }
        return $this->jsonEncoder->encode($postIds);
    }

    /**
     * Retrieve current category instance
     *
     * @return array|null
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }
}
