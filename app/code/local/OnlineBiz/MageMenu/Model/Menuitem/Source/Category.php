<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Source_Category
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Source_Category extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	protected $_options = array();
	
	
    public function toOptionArray($addEmpty = true)
    {
    	if(count($this->_options)) {
    		return $this->_options;
    	}
    	
        $parentId = OnlineBiz_MageMenu_Model_Menuitem::DEFAULT_STORE_ID;
        $recursionLevel = max(0, (int) Mage::app()->getStore()->getConfig('catalog/navigation/max_depth'));
        
        $tree = Mage::getResourceModel('catalog/category_tree');
        $nodes = $tree->loadNode($parentId)
            ->loadChildren($recursionLevel)
            ->getChildren();
        
        $tree->addCollectionData(null, false, $parentId, true, false);    
        
        foreach($nodes as $node) {
        	$this->addNode($node, 0);
        }    
        $this->_options = $this->_formatArray($this->_options);
        return $this->_options;
    }
    
    protected function _formatArray($options)
    {
        $array = array(array(
        	'label' => Mage::helper('magemenu')->__('Please select a category'), 
        	'value' => null
        ));
        
        $current = 0;
        foreach($options as $option) {
            if(false === strpos($option['label'], '&nbsp;')) {
                $array[++$current] = array(
                    'label' => $option['label'],
                    'value' => array()
                );
                continue;
            }
            
            $array[$current]['value'][] =  $option;
        }
        
        return $array;        
    }
    
    public function _addWhitespaceByLevel($name = '', $level = 0)
    {
    	if(empty($name)) {
    		return $name;
    	}
    	
        $nbsp = '';
        for($x = 1; $x < $level; $x++ ) {
        	$nbsp .= "&nbsp;&nbsp;&nbsp;&nbsp;";        	
        }
    	return $nbsp . $name;
    }

    public function addNode($node, $level=0)
    {
    	if('' != $node->getName() && $level) {
            $this->_options[] = array(
               'label' => $this->_addWhitespaceByLevel($node->getName(), $level),
               'value' => $level > 1 ? $node->getId() : null,
            );
    	}
    	
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $children = $node->getChildrenNodes();
            $childrenCount = count($children);
        } else {
            $children = $node->getChildren();
            $childrenCount = $children->count();
        }
        foreach ($children as $child) {            
        	$this->addNode($child, $level+1);
        }
        
        return;
    }
    
    
    public function getAllOptions()
    {
    	return $this->toOptionArray();
    }
}
