<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Type_Cms_Page
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Type_Cms_Page extends OnlineBiz_MageMenu_Model_Menuitem_Type_Abstract
{
	protected $_identifier = 'cms_page';
	protected $_model = 'cms/page';
		
	public function isActive()
	{
		return $this->getUrl() && $this->getData('cms_page_is_active');
	}
	
	public function getUrl($addBaseUrl = false)
	{
		return $addBaseUrl && $this->getData('cms_page_url') 
					? $this->_addBaseUrl($this->getData('cms_page_url')) 
					: $this->getData('cms_page_url');
	}
		
	public function load()
	{
		$id = $this->getData('link_to_cms_page');
		return Mage::getModel('cms/page')->load($id);
	}
	
	public function isValid()
	{	    
		return parent::isValid();
	}
	
	public function checkForWarnings()
	{
	    if( ! $this->getLinkId()) {
	        return false;
	    }
	    if($store = Mage::app()->getRequest()->getParam('store')) {
			$cmsPage = Mage::getModel($this->_model)->setStoreId(Mage::app()->getRequest()->getParam('store'))->load($this->getLinkId());
			if( ! $cmsPage->getIsActive()) {
				$this->addWarning(Mage::helper('magemenu')->__('The linked category is not active in this store view'));
			}
		} else {
			if( ! $this->_checkIsActiveInStores()) {
				$this->addWarning(Mage::helper('magemenu')->__('The linked category is not set active on all store views'));
			}
		}
		
		return parent::checkForWarnings();
	}
	
	protected function _checkIsActiveInStores()
	{
	    if( ! $this->getLinkId()) {
	        return false;
	    }
		foreach(Mage::app()->getStores(true) as $store) {
			$cmsPage = Mage::getModel($this->_model)->setStoreId($store->getId())->load($this->getLinkId());
			if( ! $cmsPage->getIsActive()) {
				return false;
			}
		}
		return true;
	}
	
}