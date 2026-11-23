<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\App\Logger;
use FishPig\WordPress\Block\ShortcodeFactory;
use FishPig\WordPress\Model\OptionRepository;
use FishPig\WordPress\App\Url;
use FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use Magento\Framework\Serialize\SerializerInterface;
use FishPig\WordPress\App\View\DirectiveFilter;

class Context
{

    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var ShortcodeFactory
     */
    private $shortcodeFactory;
    
    /**
     * @var OptionRepository
     */
    private $optionRepository;
    
    /**
     * @var Url
     */
    private $url;
    
    /**
     * @var PostCollectionFactory
     */
    private $postCollectionFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;
    
    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var DirectiveFilter
     */
    private $directiveFilter;

    /**
     * @param Registry
     */
    public function __construct(
        Logger $logger,
        ShortcodeFactory $shortcodeFactory,
        OptionRepository $optionRepository,
        Url $url,
        PostCollectionFactory $postCollectionFactory,
        SerializerInterface $serializer,
        SerializerInterface $jsonSerializer,
        DirectiveFilter $directiveFilter
    ) {
        $this->logger = $logger;
        $this->shortcodeFactory = $shortcodeFactory;
        $this->optionRepository = $optionRepository;
        $this->url = $url;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->serializer = $serializer;
        $this->jsonSerializer = $jsonSerializer;
        $this->directiveFilter = $directiveFilter;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
    
    /**
     * @return ShortcodeFactory
     */
    public function getShortcodeFactory(): ShortcodeFactory
    {
        return $this->shortcodeFactory;
    }
    
    /**
     * @return Option
     */
    public function getOptionRepository(): OptionRepository
    {
        return $this->optionRepository;
    }
    
    /**
     * @return Url
     */
    public function getUrl(): Url
    {
        return $this->url;
    }
    
    /**
     * @return PostCollectionFactory
     */
    public function getPostCollectionFactory(): PostCollectionFactory
    {
        return $this->postCollectionFactory;
    }
    
    /**
     * @return Serializer
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * @return Serializer
     */
    public function getJsonSerializer(): SerializerInterface
    {
        return $this->jsonSerializer;
    }

    /**
     * @return DirectiveFilter
     */
    public function getDirectiveFilter(): DirectiveFilter
    {
        return $this->directiveFilter;
    }
}
