<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\View;

use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;

/**
 * Renders Magento directives ({{block}}, {{widget}} etc) in content written in
 * WordPress. Only pass content from WordPress to this, never the whole page, as
 * the page can contain guest input (eg. a search term).
 */
class DirectiveFilter
{
    /**
     * @const string
     */
    const SOURCE_CONTENT = 'content';
    const SOURCE_WIDGETS = 'widgets';
    const SOURCE_SHORTCODES = 'shortcodes';
    const SOURCE_ACF = 'acf';

    /**
     * @const string
     */
    const MODE_ALL = 'all';
    const MODE_SPECIFIC = 'specific';

    /**
     * @const string
     */
    const DIRECTIVE_PATTERN = '/\{\{(block|store|trans|view|layout|inlinecss|css|customvar|config|var|template|widget) [^}]*\}\}/';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Widget\Model\Template\Filter
     */
    private $templateFilter;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Widget\Model\Template\Filter\Proxy $templateFilter
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\State $appState,
        \Magento\Widget\Model\Template\Filter\Proxy $templateFilter
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->appState = $appState;
        $this->templateFilter = $templateFilter;
    }

    /**
     * @param  string $html
     * @param  string $source one of the SOURCE_* constants
     * @return string
     */
    public function filter(string $html, string $source): string
    {
        if (strpos($html, '{{') === false || !$this->isEnabled($source)) {
            return $html;
        }

        $requestText = $this->normalise((string)$this->request->getRequestUri(), true);

        return preg_replace_callback(
            self::DIRECTIVE_PATTERN,
            function ($match) use ($requestText) {
                // HTML rendered by WordPress can include request data (eg. a search
                // term) so never render a directive that is in the request URL.
                if ($requestText !== '' && strpos($requestText, $this->normalise($match[0])) !== false) {
                    return $match[0];
                }

                return $this->templateFilter->filter($match[0]);
            },
            $html
        );
    }

    /**
     * @param  string $source
     * @return bool
     */
    private function isEnabled(string $source): bool
    {
        try {
            if ($this->appState->getAreaCode() !== Area::AREA_FRONTEND) {
                return false;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return false;
        }

        if ($this->scopeConfig->getValue('wordpress/directives/mode', ScopeInterface::SCOPE_STORE) !== self::MODE_SPECIFIC) {
            return true;
        }

        return $this->scopeConfig->isSetFlag('wordpress/directives/' . $source, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Reduce to lowercase letters and numbers so that quoting, escaping and
     * encoding differences don't stop a match.
     *
     * @param  string $s
     * @param  bool $urlDecode
     * @return string
     */
    private function normalise(string $s, bool $urlDecode = false): string
    {
        if ($urlDecode) {
            for ($i = 0; $i < 3 && ($decoded = urldecode($s)) !== $s; $i++) {
                $s = $decoded;
            }
        }

        return strtolower(preg_replace('/[^a-z0-9]+/i', '', html_entity_decode($s, ENT_QUOTES | ENT_HTML5)));
    }
}
