<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Type_Category
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Type_Category extends OnlineBiz_MageMenu_Model_Menuitem_Type_Abstract
{
	protected $_identifier = 'category';
	protected $_model = 'catalog/category';
	
	public function isActive()
	{
		return $this->getUrl() && $this->getData('category_is_active');
	}
	
	public function isValid()
	{
		if( ! $this->getData('link_to_' . $this->_identifier)) {
			$this->addError(Mage::helper('magemenu')->__('No valid category set'));
		}
				
		return parent::isValid();
	}
	
	public function getUrl($addBaseUrl = false)
	{
		if( ! $this->getData('category_url')) {
			$this->setData('category_url', "catalog/category/view/id/{$this->getData('link_to_' . $this->_identifier)}/");
		}

	    return $addBaseUrl && $this->getData('category_url') 
					? $this->_addBaseUrl($this->getData('category_url')) 
					: $this->getData('category_url');
	}
	
	public function checkForWarnings()
	{
	    if( ! $this->getLinkId()) {
	        return false;
	    }
	    if($store = Mage::app()->getRequest()->getParam('store')) {
			$menuitem = Mage::getModel($this->_model)->setStoreId(Mage::app()->getRequest()->getParam('store'))->load($this->getLinkId());
			if( ! $menuitem->getIsActive()) {
				$this->addWarning(Mage::helper('magemenu')->__('The linked category is not active in this store view and will not be displayed'));
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
			$menuitem = Mage::getModel($this->_model)->setStoreId($store->getId())->load($this->getLinkId());
			if( ! $menuitem->getIsActive()) {
				return false;
			}
		}
		return true;
	}
	
}