<?php
/**
 * TinyBrick Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the TinyBrick Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.delorumcommerce.com/license/commercial-extension
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@tinybrick.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   TinyBrick
 * @package    TinyBrick_LightSpeed
 * @copyright  Copyright (c) 2010 TinyBrick Inc. LLC
 * @license    http://store.delorumcommerce.com/license/commercial-extension
 */

class TinyBrick_LightSpeed_Block_Page_Html extends Mage_Page_Block_Html
{
	
	protected function _construct()
	{
		if (isset($_GET['debug_back']) && $_GET['debug_back'] == '1') {
			$this->setIsDebugMode(true);
		}
		return parent::_construct();
	}
	
	public function cachePage($expires='', $disqualifiers='', $disqualifiedContentPath='')
	{
		$this->setCachePage(true);
		$this->setExpires(($expires)? $expires : false);
		$this->setDisqualifiers($disqualifiers);
		$this->setDisqualifiedContentPath($disqualifiedContentPath);
		$this->setAggregateTags(array('MAGE'));
		return $this;
	}
	
	protected function _aggregateTags()
	{
		if($blocks = $this->getLayout()->getAllBlocks()) {
			$aggregateTags = $this->getAggregateTags();
			foreach($blocks as $block) {
				$tags = $block->getCacheTags();
				if(!is_array($tags)){
					$tags = array($tags);
				}
				foreach($tags as $tag){
					$tag = strtoupper($tag);
					if(!in_array($tag, $aggregateTags)){
						$aggregateTags[] = $tag;
					}
				}
			}
			$this->setAggregateTags($aggregateTags);
		}
	}
	
	protected function _afterToHtml($html)
	{
		if ($this->getCachePage()) {
			if (Mage::app()->useCache('lightspeed')) {
				if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
					if ($this->_isRegistered()) {
						if (Mage::getSingleton('checkout/cart')->getItemsCount() < 1) {
							if(!$this->_comparing()) {
								if(Mage::app()->getRequest()->getActionName() != 'noRoute') {
									$key = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
									if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
										$key = 'SECURE_' . $key;
									}
									//remove ids that should not be cached
									//get params from config to exclude from url key
									$excludeParams = Mage::getConfig()->getNode('lightspeed/global/params');
									$excludeArr = explode(",", $excludeParams);
									$excludes = array();
							    	foreach($excludeArr as $param) {
							    		$excludes[] = $param;
							    	}
							    	//see if we even need to remove some params
							    	if(count($excludes)) {
							    		//if we do, blow it up and remake it
							    		$keyPart = explode("?", $key);
										if (stristr($key,"?")) {
							    		if($keyPart[1]) {
							    			$keyParse = parse_url($key);
											$newParams = explode("&", $keyParse['query']);
											$newString = '?';
											foreach($newParams as $iteration=>$newParam) {
												$paramArr = explode("=", $newParam);
												if(!in_array($paramArr[0], $excludes)) {
													$newString .= $paramArr[0] . "=" . $paramArr[1] . "&";
												}
												$key = $keyPart[0] . $newString;
												$key = rtrim($key, "&");
												$key = preg_replace('/(\?|&|&&)debug_back=1/s', '', $key);
											}
										}
										}
							    	} else {
							    		$key = preg_replace('/(\?|&|&&)debug_back=1/s', '', $key);
							    	}
	
									if($this->_useMultiCurrency()){
										$key .= '_' . Mage::app()->getStore()->getCurrentCurrencyCode();
									}
									// remove pesky session id from url
									Mage::app()->setUseSessionVar(false);
									$html = Mage::getSingleton('core/url')->sessionUrlVar($html);
									$data = array((string)$html, $this->getDisqualifiers(), $this->getDisqualifiedContentPath());
									$this->_aggregateTags();
										$holecontent = $this->getDisqualifiedContentPath();
									$this->report("hole content: $holecontent, saving page with key: $key",true);									
							
									Mage::getSingleton('lightspeed/server')->save($key, $data, $this->getExpires(), $this->getAggregateTags());
								} else {
									$this->report("404 page", true);
								}
							} else {
								$this->report("found items in the compare", true);
							}
						} else {
							$this->report("found items in the cart", true);
						}
					} else {
						$this->report("invalid registration", true);
					}
				} else {
					$this->report("customer is logged in", true);
				}
			} else {
				$this->report("please enable the 'whole pages' cache checkbox in the cache management panel", true);
			}
		} else {
			$this->report("this page is not set to be cached according to the layout", true);
		}
		// remove any NOCACHE tags
		$html = preg_replace('/\<!\-\- +nocache.+?\-\-\>/si', "", $html);
		$html = preg_replace('/\<!\-\- endnocache \-\-\>/si', "", $html);
		return parent::_afterToHtml($html);
	}
	
	protected function _comparing()
	{
		$comparing = false;
		if(Mage::getSingleton('catalog/session')->getCatalogCompareItemsCount()){
			if(Mage::getSingleton('catalog/session')->getCatalogCompareItemsCount() > 0){
				$comparing = true;
			}
		}
		return $comparing;
	}
	
	protected function _getConfig($key)
	{
		return Mage::getStoreConfig($key);
	}
	
	protected function _useMultiCurrency()
	{
		if($useCurrency = Mage::getConfig()->getNode('lightspeed/global/multi_currency')){
			if($useCurrency == '1'){
				return true;
			}
		}
		return false;
	}
	
	private function _isRegistered()
	{
		$baseUrl = Mage::getBaseUrl();
		if(preg_match('/127.0.0.1|localhost|192.168/', $baseUrl)){
			return true;
		}
		if($registeredDomain = $this->_getConfig('dfv/oej/nfg')){
			if(preg_match("/$registeredDomain/", $baseUrl)){
				if(($serial = $this->_getConfig('dfv/oej/wdf')) && $key = $this->_getConfig('dfv/oej/ntr')){
					if(md5($registeredDomain.$serial) == $key){
						return true;
					} 
				}
			}
		}
		return false;
	}
	
	private function report($message, $term=false)
	{
		if ($this->getIsDebugMode()) {
			echo "$message<br />";
			if ($term) {
				exit;
			}
		}
	}
	
}

?>
