<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Source_Cms
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Source_Cms extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{ 
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray($addEmpty = true)
    {
        $storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
        $options = array();
                
        if ($addEmpty) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please select a cms page --'),
                'value' => null
            );
        }
        
        $collection = Mage::getResourceModel('magemenu/menuitem')->getCmsPageByStore($storeId);
        $pages = $used = array();
        foreach ($collection as $page) {
            $pages[$page['store_id']][$page['page_id']] = array(
               'label' => $page['store_id'] ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $page['title'] : $page['title'],
               'value' => $page['page_id']
            );
        }

        if(isset($pages[0]) && count($pages[0])) {
	        $options[] = array(
	            'label' => 'All Store Views',
	            'value' => $pages[0],
	        );
        }

        foreach(Mage::app()->getWebsites() as $website) {
            $options[$website->getId()+1] = array(
                'label' => $website->getName(),
                'value' => null
            );
            foreach($website->getGroups() as $group) {
	            $options[$website->getId()+1]['value'][$group->getId()] = array(
	                'label' => '&nbsp;&nbsp;' . $group->getName(),
	                'value' => null
	            );
                foreach($group->getStores() as $store) {
                    if( ! isset($pages[$store->getId()]) || ! count($pages[$store->getId()])) {
                        continue;
                    }
	                $options[$website->getId()+1]['value'][$group->getId()]['value'][$store->getId()] = array(
	                    'label' => '&nbsp;&nbsp;&nbsp;&nbsp;' . $store->getName(),
	                    'value' => ! isset($pages[$store->getId()]) ? array() : $pages[$store->getId()]
	                );
	            }
	            if( ! is_array($options[$website->getId()+1]['value'][$group->getId()]['value'])
	                || ! count($options[$website->getId()+1]['value'][$group->getId()]['value'])) {
	                unset($options[$website->getId()+1]['value'][$group->getId()]);
	            }
	        }
            if( ! is_array($options[$website->getId()+1]['value']) || ! count($options[$website->getId()+1]['value'])) {
                unset($options[$website->getId()+1]);
            }
        }
       
        return $options;
    }
    
    public function getAllOptions()
    {
    	return $this->toOptionArray();
    }
    
}