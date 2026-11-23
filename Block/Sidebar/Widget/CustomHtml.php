<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class CustomHtml extends AbstractWidget
{
    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->directiveFilter->filter(
            (string)$this->getData('content'),
            \FishPig\WordPress\App\View\DirectiveFilter::SOURCE_WIDGETS
        );
    }

    /**
     * Render html output
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::sidebar/widget/custom-html.phtml');
        }

        return parent::_beforeToHtml();
    }
}
