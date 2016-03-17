<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Type_Product
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Type_Product extends OnlineBiz_MageMenu_Model_Menuitem_Type_Abstract
{
	protected $_identifier = 'product';
	protected $_model = 'catalog/product';
		
	public function isActive()
	{
		return $this->getUrl() && $this->getData('product_is_active');
	}
	
	public function getUrl($addBaseUrl = false)
	{
		if( ! $this->getData('product_url')) {
			$this->setData('product_url', "catalog/product/view/id/{$this->getData('product_id')}/");
		}
	    return $addBaseUrl && $this->getData('product_url') 
					? $this->_addBaseUrl($this->getData('product_url')) 
					: $this->getData('product_url');
	}
		
	public function isValid()
	{
		if( ! $this->getData('link_to_' . $this->_identifier)) {
			$this->addError(Mage::helper('magemenu')->__('No valid product set'));
		}
		
		$product = Mage::getModel($this->_model)->loadByAttribute('sku', $this->getLinkId());
		if( ! ($product instanceof Mage_Catalog_Model_Product) || is_null($product->getId())) {
			$this->addError(Mage::helper('magemenu')->__('Invalid Product Sku'));
		}
		
		return parent::isValid();
	}
	
	public function checkForWarnings()
	{
	    if( ! $this->getLinkId()) {
	        return false;
	    }
	    
		if($store = Mage::app()->getRequest()->getParam('store')) {
			$product = Mage::getModel($this->_model)->setStoreId(Mage::app()->getRequest()->getParam('store'))->loadByAttribute('sku', $this->getLinkId());
			if( ! $product->getIsActive()) {
				$this->addWarning(Mage::helper('magemenu')->__('The linked product is not active in this store view'));
			}
		} else {
			if( ! $this->_checkIsActiveInStores()) {
				$this->addWarning(Mage::helper('magemenu')->__('The linked product is not set active on all store views'));
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
			$product = Mage::getModel($this->_model)->setStoreId($store->getId())->loadByAttribute('sku', $this->getLinkId());
			
			if( ! ($product instanceof Mage_Catalog_Model_Product) || ! $product->getIsActive()) {
				return false;
			}
		}
		return true;
	}
}