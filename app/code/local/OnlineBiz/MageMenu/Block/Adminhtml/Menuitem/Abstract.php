<?php
/**
 * OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Abstract
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Abstract extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getMenu()
    {
        return Mage::registry('current_menuitem');
    }

    public function getMenuId()
    {
        if ($this->getMenu()) {
            return $this->getMenu()->getId();
        }
        return OnlineBiz_MageMenu_Model_Abstract::TREE_ROOT_ID;
    }

    public function getMenuName()
    {
        return $this->getMenu()->getName();
    }


    public function hasRootMenu()
    {
        $root = $this->getRoot();
        if (is_object($root) && $root->getId()) {
            return true;
        }
        return false;
    }

    public function getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        return Mage::app()->getStore($storeId);
    }

    public function getRoot($parentNodeMenu=null, $recursionLevel=3)
    {
        if (!is_null($parentNodeMenu) && $parentNodeMenu->getId()) {
            return $this->getNode($parentNodeMenu, $recursionLevel);
        }
        
        $root = Mage::registry('root');
        if (is_null($root)) {
            $storeId = (int) $this->getRequest()->getParam('store');
			$rootId = OnlineBiz_MageMenu_Model_Abstract::TREE_ROOT_ID;
            $tree = Mage::getResourceSingleton('magemenu/menuitem_tree')
                ->load(null, $recursionLevel);
                
            if ($this->getMenu()) {
                $tree->loadEnsuredNodes($this->getMenu(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getMenuCollection());
            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != OnlineBiz_MageMenu_Model_Abstract::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            }
            elseif($root && $root->getId() == OnlineBiz_MageMenu_Model_Abstract::TREE_ROOT_ID) {
                $root->setName(Mage::helper('magemenu')->__('Root'));
            }

            Mage::register('root', $root);
        }

        return $root;
    }


    public function getNode($parentNodeMenu, $recursionLevel=2)
    {
        $tree = Mage::getResourceModel('magemenu/menuitem_tree');

        $nodeId     = $parentNodeMenu->getId();
        $parentId   = $parentNodeMenu->getParentId();

        $node = $tree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);

        if ($node && $nodeId != OnlineBiz_MageMenu_Model_Abstract::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif($node && $node->getId() == OnlineBiz_MageMenu_Model_Abstract::TREE_ROOT_ID) {
            $node->setName(Mage::helper('magemenu')->__('Root'));
        }
        
        $tree->addCollectionData($this->getMenuCollection());

        return $node;
    }

    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if (is_null($ids)) {
            $ids = Mage::getResourceModel('magemenu/menuitem')->getRootIds();
            $this->setData('root_ids', $ids);
        }
        return $ids;
    }
    public function getSaveUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/save', $params);
    }

    public function getEditUrl()
    {
        return $this->getUrl("*/adminhtml_menuitem/edit", array('_current'=>true, 'store'=>null, '_query'=>false, 'id'=>null, 'parent'=>null));
    }
}