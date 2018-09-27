<?php
/**
 * @package FishPig_WordPress
 * @author Josh Carter <josh@interjar.com>
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Adminhtml\Catalog\Product\Edit\Tab;

use FishPig\WordPress\Api\Data\PostAssociationInterface;
use FishPig\WordPress\Api\Data\PostAssociationSearchResultsInterface;
use FishPig\WordPress\Api\PostAssociationRepositoryInterface;
use FishPig\WordPress\Model\Post\Source\PostStatus;
use FishPig\WordPress\Model\Post\Source\PostType;
use FishPig\WordPress\Model\ResourceModel\Post\Collection;
use FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Registry;

class PostAssociation extends Extended
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var CollectionFactory
     */
    private $postcollectionFactory;

    /**
     * @var PostAssociationRepositoryInterface
     */
    private $postAssociationRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var PostType
     */
    private $postTypeSource;

    /**
     * @var PostStatus
     */
    private $postStatusSource;

    /**
     * PostAssociation constructor
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param Registry $registry
     * @param CollectionFactory $postCollectionFactory
     * @param PostAssociationRepositoryInterface $postAssociationRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param PostType $postTypeSource
     * @param PostStatus $postStatusSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $registry,
        CollectionFactory $postCollectionFactory,
        PostAssociationRepositoryInterface $postAssociationRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        PostType $postTypeSource,
        PostStatus $postStatusSource,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->coreRegistry = $registry;
        $this->postcollectionFactory = $postCollectionFactory;
        $this->postAssociationRepository = $postAssociationRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->postTypeSource = $postTypeSource;
        $this->postStatusSource = $postStatusSource;
    }

    /**
     * Set various data properties of Grid
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('associatedPostsGrid');
        $this->setDefaultSort('ID');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_posts') {
            $postIds = $this->_getSelectedPosts();
            if (empty($postIds)) {
                $postIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter(
                    'ID',
                    array(
                        'in' => $postIds
                    )
                );
            } else {
                if ($postIds) {
                    $this->getCollection()->addFieldToFilter(
                        'ID',
                        array(
                            'nin' => $postIds
                        )
                    );
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Create/Prepare Post Collection instance
     */
    protected function _prepareCollection()
    {
        if ($this->getProduct()->getId()) {
            $this->setDefaultFilter(['in_posts' => 1]);
        }
        $collection = $this->postcollectionFactory
            ->create()
            ->addFieldToSelect('*');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid Columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_posts',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_posts',
                'align' => 'center',
                'index' => 'ID',
                'values' => $this->_getSelectedPosts(),
            ]
        );

        $this->addColumn('ID', array(
            'header' => __('ID'),
            'sortable' => true,
            'width' => 60,
            'index' => 'ID'
        ));

        $this->addColumn('post_title', array(
            'header' => __('Post Title'),
            'index' => 'post_title'
        ));

        $this->addColumn('post_type', array(
            'header' => __('Post Type'),
            'index' => 'post_type',
            'type' => 'options',
            'options' => $this->postTypeSource->getOptionArray()
        ));

        $this->addColumn('post_status', array(
            'header' => __('Post Status'),
            'index' => 'post_status',
            'type' => 'options',
            'options' => $this->postStatusSource->getOptionArray()
        ));

        return parent::_prepareColumns();
    }

    /**
     * Return Grid Ajax URL
     *
     * @return string
     */
    public function getGridUrl(): string
    {
        return $this->getUrl(
            'posts/product/associationGrid',
            [
                '_current' => true
            ]
        );
    }

    /**
     * Return current Product from Registry
     *
     * @return mixed
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Return Associatied Post IDs
     *
     * @return array
     */
    protected function _getSelectedPosts(): array
    {
        return $this->getSelectedPosts();
    }

    /**
     * Return Associatied Post IDs
     *
     * @return array
     */
    public function getSelectedPosts(): array
    {
        $postIds = $this->getRequest()->getPost('selected_posts');
        if ($postIds === null) {
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
                    $postIds[] = $postAssociation->getPostId();
                }
            }
        }
        return $postIds;
    }
}
