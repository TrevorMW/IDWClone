<?php
/**
 * OnlineBiz_MageMenu_Block_Menuitem_List
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Block_Menuitem_List extends Mage_Core_Block_Template
{
    protected $_collection = null;
    
    public function getMenuCodes()
    {
    	if(is_null(Mage::registry('magemenu_menuitem_codes'))) {    		
    		$collection = Mage::getModel('magemenu/menuitem')->getCollection();
    		$collection->addAttributeToSelect('*')
    				   ->addLevelFilter(1)
    				   ->addIsActiveFilter()
    				   ->addAttributeToSort('sort_order');
    				   
    		Mage::register('magemenu_menuitem_codes', $collection);
    	}
        return Mage::registry('magemenu_menuitem_codes');
    }

    protected function _toHtml()
    {
    	$menuCodes = $this->getMenuCodes();
    	
    	if(is_null($menuCodes) || ! count($menuCodes)) {
    		return '';
    	}
    	
    	$blocks = array();
    	foreach($menuCodes as $menu) {	    		
    		
    		$code = $menu->getData('menu_code');
    		$position = $menu->getData('menu_position');
    		
    		if($this->getBlockId() != $position) {
    			continue;
    		}
    		
    		$blocks[$code] = $this->getLayout()
    						    ->createBlock('magemenu/menuitem', "{$position}-{$code}", array('position' => 999999))
    						 	->setInstance($menu)
   								->toHtml();
    	}
    	return implode("<!-- BLOCK -->", $blocks);    	
    }
}