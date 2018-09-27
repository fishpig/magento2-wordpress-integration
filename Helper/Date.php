<?php
/*
 *
 */
namespace FishPig\WordPress\Helper;

/* Parent Class */
use Magento\Framework\App\Helper\AbstractHelper;

/* Constructor Args */
use Magento\Framework\App\Helper\Context;
use FishPig\WordPress\Model\OptionManager;

class Date extends AbstractHelper
{
	/*
	 * @Var OptionManager
	 */
	protected $optionManager;

	/*
	 *
	 */
	public function __construct(Context $context, OptionManager $optionManager)
	{
		parent::__construct($context);

		$this->optionManager = $optionManager;
	}

	
	/*
	 * Formats a Wordpress date string
	 *
	 * @return
	 */
	public function formatDate($date, $format = null, $f = false)
	{
		if ($format == null) {
			$format = $this->getDefaultDateFormat();
		}
		
		/**
		 * This allows you to translate month names rather than whole date strings
		 * eg. "March","Mars"
		 *
		 */
		$len = strlen($format);
		$out = '';
		
		for( $i = 0; $i < $len; $i++) {	
			$out .= __(date($format[$i], strtotime($date)));
		}
		
		return $out;
	}
	
	/*
	 * Formats a Wordpress date string
	 *
	 */
	public function formatTime($time, $format = null)
	{
		if ($format == null) {
			$format = $this->getDefaultTimeFormat();
		}
		
		return $this->formatDate($time, $format);
	}
	
	/*
	 * Split a date by spaces and translate
	 *
	 * @param string $date
	 * @param string $splitter = ' '
	 * @return string
	 */
	public function translateDate($date, $splitter = ' ')
	{
		$dates = explode($splitter, $date);
		
		foreach($dates as $it => $part) {
			$dates[$it] = __($part);
		}
		
		return implode($splitter, $dates);
	}
	
	/*
	 * Return the default date formatting
	 *
	 */
	public function getDefaultDateFormat()
	{
		if ($format = $this->optionManager->getOption('date_format')) {
			return $format;
		}
		
		return 'F jS, Y';
	}
	
	/*
	 * Return the default time formatting
	 *
	 */
	public function getDefaultTimeFormat()
	{
		if ($format = $this->optionManager->getOption('time_format')) {
			return $format;
		}

		return 'g:ia';
	}
}
