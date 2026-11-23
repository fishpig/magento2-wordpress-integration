<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block;

use Magento\Framework\Registry;
use FishPig\WordPress\App\Logger;
use FishPig\WordPress\Block\ShortcodeFactory;
use FishPig\WordPress\Model\OptionRepository;
use FishPig\WordPress\App\Url;
use Magento\Framework\Serialize\SerializerInterface;
use FishPig\WordPress\Helper\BlogInfo;
use FishPig\WordPress\App\Integration\Tests\Proxy as IntegrationTests;
use Magento\Framework\App\State as AppState;
use FishPig\WordPress\App\View\DirectiveFilter;

class Context
{
    /**
     * @var Registry
     */
    private $registry;

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var BlogInfo
     */
    private $blogInfo;

    /**
     * @var IntegrationTests
     */
    private $integrationTests;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var DirectiveFilter
     */
    private $directiveFilter;

    /**
     * @param Registry
     */
    public function __construct(
        Registry $registry,
        Logger $logger,
        ShortcodeFactory $shortcodeFactory,
        OptionRepository $optionRepository,
        Url $url,
        SerializerInterface $serializer,
        SerializerInterface $jsonSerializer,
        BlogInfo $blogInfo,
        IntegrationTests $integrationTests,
        AppState $appState,
        DirectiveFilter $directiveFilter
    ) {
        $this->directiveFilter = $directiveFilter;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->shortcodeFactory = $shortcodeFactory;
        $this->optionRepository = $optionRepository;
        $this->url = $url;
        $this->serializer = $serializer;
        $this->jsonSerializer = $jsonSerializer;
        $this->blogInfo = $blogInfo;
        $this->integrationTests = $integrationTests;
        $this->appState = $appState;
    }

    /**
     * @return Registry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
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
     * @return BlogInfo
     */
    public function getBlogInfo(): BlogInfo
    {
        return $this->blogInfo;
    }

    /**
     *
     */
    public function getIntegrationTests(): IntegrationTests
    {
        return $this->integrationTests;
    }

    /**
     * @return AppState
     */
    public function getAppState(): AppState
    {
        return $this->appState;
    }

    /**
     * @return DirectiveFilter
     */
    public function getDirectiveFilter(): DirectiveFilter
    {
        return $this->directiveFilter;
    }
}
