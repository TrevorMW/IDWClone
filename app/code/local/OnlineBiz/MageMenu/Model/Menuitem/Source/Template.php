<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Source_Template
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Source_Template extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{ 
	const TEMPLATE_HORIZONTAL 		= 'onlinebizsoft/magemenu/horizontal.phtml';
	const TEMPLATE_EXPLODED 		= 'onlinebizsoft/magemenu/exploded.phtml';
	const TEMPLATE_VERTICAL 		= 'onlinebizsoft/magemenu/vertical.phtml';
	const TEMPLATE_TREE 			= 'onlinebizsoft/magemenu/tree.phtml';
	const TEMPLATE_LINK_LIST 		= 'onlinebizsoft/magemenu/link_list.phtml';
	
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
    	return array(
    		self::TEMPLATE_HORIZONTAL 	=> Mage::helper('magemenu')->__('Horizontal'),
    		self::TEMPLATE_EXPLODED 	=> Mage::helper('magemenu')->__('Exploded'),
    		self::TEMPLATE_TREE 		=> Mage::helper('magemenu')->__('Tree'),
    		self::TEMPLATE_VERTICAL 	=> Mage::helper('magemenu')->__('Vertical'),
    		self::TEMPLATE_LINK_LIST 	=> Mage::helper('magemenu')->__('Link List'),
    	);
    }
    
    public function getAllOptions()
    {
    	return $this->getOptionArray();
    }
}