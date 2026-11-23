<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block;

use Magento\Framework\App\State as AppState;

abstract class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @auto
     */
    protected $logger = null;

    /**
     * @auto
     */
    protected $optionRepository = null;

    /**
     * @auto
     */
    protected $url = null;

    /**
     * @auto
     */
    protected $integrationTests = null;

    /**
     * @auto
     */
    protected $appState = null;

    /**
     * @auto
     */
    protected $wpContext = null;

    /**
     * @var OptionManager
     */
    protected $optionManager;

    /**
     * @var \FishPig\WordPress\Block\ShortcodeFactory
     */
    protected $shortcodeFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \FishPig\WordPress\App\View\DirectiveFilter
     */
    protected $directiveFilter;

    /**
     * @param  \Magento\Framework\View\Element\Template\Context $context,
     * @param  \FishPig\WordPress\Block\Context $wpContext,
     * @param  array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Block\Context $wpContext,
        array $data = []
    ) {
        $this->logger = $wpContext->getLogger();
        $this->registry = $wpContext->getRegistry();
        $this->shortcodeFactory = $wpContext->getShortcodeFactory();
        $this->optionRepository = $wpContext->getOptionRepository();
        $this->url = $wpContext->getUrl();
        $this->integrationTests = $wpContext->getIntegrationTests();
        $this->appState = $wpContext->getAppState();
        $this->directiveFilter = $wpContext->getDirectiveFilter();
        parent::__construct($context, $data);
    }

    /**
     * @param  string $shortcode
     * @param  \Magento\Framework\DataObject  $object = null
     * @return string
     */
    public function renderShortcode($shortcode, $object = null)
    {
        return $this->shortcodeFactory->create(
            /**/
        )->setShortcode(
            $shortcode
        )->setPost(
            $object
        )->toHtml();
    }

    /**
     * @param  string $shortcode
     * @param  \Magento\Framework\DataObject  $object = null
     * @return string
     */
    public function doShortcode($shortcode, $object = null)
    {
        return $this->renderShortcode($shortcode, $object);
    }

    /**
     * Catch and log any exceptions to var/log/wp/error.log
     */
    public function toHtml()
    {
        try {
            if ($this->integrationTests->runTests() !== false){
                return parent::toHtml();
            }
        } catch (\Exception $e) {
            $this->logger->error($e);

            if ($this->isDeveloperMode()) {
                return sprintf(
                    '<strong>Exception:</strong> %s<br/><br/><pre>%s</pre>',
                    $e->getMessage(),
                    str_replace(BP . '/', '', $e->getTraceAsString())
                );
            }

            return 'An error has happened during application run. See var/log/wp/error.log for details';
        }
    }

    /**
     * @return bool
     */
    protected function isDeveloperMode(): bool
    {
        return $this->appState->getMode() === AppState::MODE_DEVELOPER;
    }

    /**
     * Adds in a WP cache tag
     * @return array
     */
    protected function getCacheTags()
    {
        $tags = array_merge(
            parent::getCacheTags(),
            [
                \FishPig\WordPress\Model\AbstractModel::CACHE_TAG_WP
            ]
        );

        return $tags;
    }
}
