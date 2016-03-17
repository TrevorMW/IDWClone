<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Source_Type
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Source_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{ 
	const TYPE_NOWHERE 		= 'nowhere';
	const TYPE_CATEGORY 	= 'category';
	const TYPE_CMS_PAGE 	= 'cms_page';
	const TYPE_PRODUCT 		= 'product';
	const TYPE_INTERNAL 	= 'internal';
	const TYPE_EXTERNAL 	= 'external';
	
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
    	return array(
    		self::TYPE_NOWHERE 		=> Mage::helper('magemenu')->__('Nowhere'),
    		self::TYPE_CATEGORY 	=> Mage::helper('magemenu')->__('Category'),
    		self::TYPE_CMS_PAGE 	=> Mage::helper('magemenu')->__('CMS Page'),
    		self::TYPE_PRODUCT 		=> Mage::helper('magemenu')->__('Product'),
    		self::TYPE_INTERNAL 	=> Mage::helper('magemenu')->__('Internal Url'),
    		self::TYPE_EXTERNAL 	=> Mage::helper('magemenu')->__('External Url'),
    	);
    }
    
    public function getAllOptions()
    {
    	return $this->getOptionArray();
    }
    
    public function getTypes()
    {
    	return array_keys($this->getOptionArray());
    }
}