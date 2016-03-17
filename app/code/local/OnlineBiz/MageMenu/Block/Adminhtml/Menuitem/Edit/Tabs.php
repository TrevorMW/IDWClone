<?php
/**
 * OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Edit_Tabs
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	
	protected $_attributes = array(
		// General tab
		'name',
		'menu_code',
		'is_active',
		'menu_position',
		'custom_position',
		'sort_order',
		
	
		// Design tab
		'template',
		'display_name',
		'popup_width',
		'popup_column',
		'popup_column_width',
		'image',
		'custom_font_color',
		'custom_css_class',		
				
	);
		
	protected $_itemAttributes = array(
		'menu_code',
		'menu_position',
		'custom_position',
		'sort_order',
		'template',
		'popup_width',
		'popup_column',
		'popup_column_width',
		'display_name',			
	);
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('menu_info_tabs');
        $this->setDestElementId('menu_tab_content');
        $this->setTitle(Mage::helper('magemenu')->__('Menu Data'));
        $this->setTemplate('widget/tabshoriz.phtml');
    }
	
    protected function _prepareLayout()
    {  	
    	$menu = Mage::registry('current_menuitem');
	   
        $menuitemAttributes = $this->_getCategoryAttributes($menu->getAttributes(), $menu->isRoot());
        $attributeSetId     = $menu->getDefaultAttributeSetId();
        $groupCollection    = Mage::getResourceModel('eav/entity_attribute_group_collection')
            ->setAttributeSetFilter($attributeSetId)
            ->load();
        $defaultGroupId = 0;
        $after = null;
        foreach ($groupCollection as $group) {
            if ($defaultGroupId == 0 or $group->getIsDefault()) {
                $defaultGroupId = $group->getId();
            }
            if(strcmp($group->getAttributeGroupName(), 'General Information') === 0) {
            	$after = 'group_' . $group->getId();
            }
        }

        foreach ($groupCollection as $group) {
            $attributes = array();
            foreach ($menuitemAttributes as $attribute) {
                if ($attribute->isInGroup($attributeSetId, $group->getId())) {
                    $attributes[] = $attribute;
                }
            }

            if (!$attributes) {            	
                continue;
            }

            $active  = $defaultGroupId == $group->getId();
            $block = $this->getLayout()->createBlock($this->getTabBlock(), '')
                ->setGroup($group)
                ->setAttributes($attributes)
                ->setAddHiddenFields($active)
                ->toHtml();
                
            $this->addTab('group_' . $group->getId(), array(
                'label'     => Mage::helper('magemenu')->__($group->getAttributeGroupName()),
                'content'   => $block,
                'active'    => $active,
            	'after'		=> $after,
            ));
        }
    	
        Mage::dispatchEvent('magemenu_adminhtml_menuitem_edit_tabs', array(
            'tabs'  => $this,
        	'isRoot' => $menu->isRoot(),
        ));

        return parent::_prepareLayout();
    }
	
    public function getMenu()
    {
        return Mage::registry('current_menuitem');
    }

    public function getTabBlock()
    {
    	return $this->getLayout()->createBlock('magemenu/adminhtml_menuitem_edit_tab_attributes');
    }

    
    
    protected function _getCategoryAttributes($attributes, $isRoot = false)
    {
		$displayAttributes = array();
		
    	if($isRoot) {	    	
	    	foreach($this->_attributes as $code) {
	    		if(isset($attributes[$code])) {
	    			$displayAttributes[$code] = $attributes[$code];
	    		}
	    	}	    	
    	} else {
    		foreach($attributes as $code => $attribute) {
    			if( ! in_array($code, $this->_itemAttributes)) {
    				$displayAttributes[$code] = $attribute;
    			}
    		}
    	}
    	return $displayAttributes;
    	
    }

}

