<?php
/**
 * OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Menuitem
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Menuitem 
	extends OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Abstract
{

	protected $_rootIds;

    protected $_tree;

    protected $_isActiveAttributeId = null;

    protected $_isRestrictedAccessAttributeId = null;

    protected $_storeId = null;


    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType('magemenu')
            ->setConnection(
                $resource->getConnection('magemenu_read'),
                $resource->getConnection('magemenu_write')
            );
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }


    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            return Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    protected function _getTree()
    {
        if (!$this->_tree) {
            $this->_tree = Mage::getResourceModel('magemenu/menuitem_tree')
                ->load();
        }
        return $this->_tree;
    }

    protected function _beforeDelete(Varien_Object $object)
    {
        parent::_beforeDelete($object);

        $parentIds = $object->getParentIds();
        $childDecrease = $object->getChildrenCount() + 1; // +1 is itself
        $this->_getWriteAdapter()->update(
            $this->getEntityTable(),
            array('children_count'=>new Zend_Db_Expr('`children_count`-'.$childDecrease)),
            $this->_getWriteAdapter()->quoteInto('entity_id IN(?)', $parentIds)
        );

        $select = $this->_getWriteAdapter()->select()
            ->from($this->getEntityTable(), array('entity_id'))
            ->where($this->_getWriteAdapter()->quoteInto('`path` LIKE ?', $object->getPath().'/%'));

        $childrenIds = $this->_getWriteAdapter()->fetchCol($select);

        if (!empty($childrenIds)) {
            $this->_getWriteAdapter()->delete(
                $this->getEntityTable(),
                $this->_getWriteAdapter()->quoteInto('entity_id IN (?)', $childrenIds)
            );
        }

        $object->setDeletedChildrenIds($childrenIds);
        return $this;
    }

    protected function _beforeSave(Varien_Object $object)
    {
        parent::_beforeSave($object);

        if (!$object->getId()) {
            $object->setPosition($this->_getMaxPosition($object->getPath()) + 1);
            $path  = explode('/', $object->getPath());
            $level = count($path);
            $object->setLevel($level);
            if ($level) {
                $object->setParentId($path[$level - 1]);
            }
            $object->setPath($object->getPath() . '/');

            $toUpdateChild = explode('/',$object->getPath());

            $this->_getWriteAdapter()->update(
                $this->getEntityTable(),
                array('children_count'=>new Zend_Db_Expr('`children_count`+1')),
                $this->_getWriteAdapter()->quoteInto('entity_id IN(?)', $toUpdateChild)
            );

        }
        return $this;
    }

    protected function _afterSave(Varien_Object $object)
    {
        if (substr($object->getPath(), -1) == '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->_savePath($object);
        }

        Mage::app()->cleanCache(array(OnlineBiz_MageMenu_Model_Menuitem::CACHE_TAG, OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Menuitem_Tree::CACHE_TAG));
        return parent::_afterSave($object);
    }

    protected function _savePath($object)
    {
        if ($object->getId()) {
            $this->_getWriteAdapter()->update(
                $this->getEntityTable(),
                array('path'=>$object->getPath()),
                $this->_getWriteAdapter()->quoteInto('entity_id=?', $object->getId())
            );
        }
        return $this;
    }

    protected function _getMaxPosition($path)
    {
        $select = $this->getReadConnection()->select();
        $select->from($this->getTable('magemenu/menuitem'), 'MAX(position)');
        $select->where('path ?', new Zend_Db_Expr("regexp '{$path}/[0-9]+\$'"));

        $result = 0;
        try {
            $result = (int) $this->getReadConnection()->fetchOne($select);
        } catch (Exception $e) {

        }
        return $result;
    }

    public function getStoreIds($menuitem)
    {
        if (!$menuitem->getId()) {
            return array();
        }

        $nodePath = $this->_getTree()
            ->getNodeById($menuitem->getId())
                ->getPath();

        $nodes = array();
        foreach ($nodePath as $node) {
            $nodes[] = $node->getId();
        }

        $stores = array();
        $storeCollection = Mage::getModel('core/store')->getCollection()->loadByCategoryIds($nodes);
        foreach ($storeCollection as $store) {
            $stores[$store->getId()] = $store->getId();
        }

        $entityStoreId = $menuitem->getStoreId();
        if (!in_array($entityStoreId, $stores)) {
            array_unshift($stores, $entityStoreId);
        }
        if (!in_array(0, $stores)) {
            array_unshift($stores, 0);
        }
        return $stores;
    }

    /**
     * Get chlden menus count
     *
     * @param   int $menuitemId
     * @return  int
     */
    public function getChildrenCount($menuitemId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getEntityTable(), 'children_count')
            ->where('entity_id=?', $menuitemId);

        $child = $this->_getReadAdapter()->fetchOne($select);

        return $child;
    }

    /**
     * Move menu item to another parent
     *
     * @param   int $menuitemId
     * @param   int $newParentId
     */
    public function move($menuitemId, $newParentId)
    {
        $menuitem  = Mage::getModel('magemenu/menuitem')->load($menuitemId);
        $oldParent = $menuitem->getParentCategory();
        $newParent = Mage::getModel('magemenu/menuitem')->load($newParentId);

        $childrenCount = $this->getChildrenCount($menuitem->getId()) + 1;

        $parentIds = explode('/', $newParent->getPath());
        $this->_getWriteAdapter()->update(
            $this->getEntityTable(),
            array('children_count' => new Zend_Db_Expr("`children_count` + {$childrenCount}")),
            $this->_getWriteAdapter()->quoteInto('entity_id IN (?)', $parentIds)
        );

          $parentIds = explode('/', $oldParent->getPath());
          $this->_getWriteAdapter()->update(
            $this->getEntityTable(),
            array('children_count' => new Zend_Db_Expr("`children_count` - {$childrenCount}")),
            $this->_getWriteAdapter()->quoteInto('entity_id IN (?)', $parentIds)
        );

        $this->_getWriteAdapter()->query("UPDATE
            {$this->getEntityTable()} SET parent_id = {$newParent->getId()}
            WHERE entity_id = {$menuitemId}");

        return $this;
    }

    /**
     * Check if menuitem id exist
     *
     * @param   int $id
     * @return  bool
     */
    public function checkMenuitemId($id)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where('entity_id=?', $id);
        return $this->_getReadAdapter()->fetchOne($select);
    }

    public function verifyMenuitemIds(array $ids)
    {
        $validIds = array();
        $select = $this->_getWriteAdapter()->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where('entity_id IN(?)', $ids);
        $query = $this->_getWriteAdapter()->query($select);
        while ($row = $query->fetch()) {
            $validIds[] = $row['entity_id'];
        }
        return $validIds;
    }

    /**
     * Get count of active/not active children menus
     *
     * @param   OnlineBiz_MageMenu_Model_Menuitem $menuitem
     * @param   bool $isActiveFlag
     * @return  int
     */
    public function getChildrenAmount($menuitem, $isActiveFlag = true)
    {
        $storeId = Mage::app()->getStore()->getId();
        $attributeId = $this->_getIsActiveAttributeId();
        $table = Mage::getSingleton('core/resource')->getTableName('magemenu/menuitem') . '_int';

        $select = $this->_getReadAdapter()->select()
            ->from(array('m'=>$this->getEntityTable()), array('COUNT(m.entity_id)'))
            ->joinLeft(
                array('d'=>$table),
                "d.attribute_id = '{$attributeId}' AND d.store_id = 0 AND d.entity_id = m.entity_id",
                array()
            )
            ->joinLeft(
                array('c'=>$table),
                "c.attribute_id = '{$attributeId}' AND c.store_id = '{$storeId}' AND c.entity_id = m.entity_id",
                array()
            )
            ->where('m.path like ?', $menuitem->getPath() . '/%')
            ->where('(IFNULL(c.value, d.value) = ?)', $isActiveFlag);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Get "is_active" attribute identifier
     *
     * @return int
     */
    protected function _getIsActiveAttributeId()
    {
        if (is_null($this->_isActiveAttributeId)) {
            $select = $this->_getReadAdapter()->select()
                ->from(array('a'=>$this->getTable('eav/attribute')), array('attribute_id'))
                ->join(array('t'=>$this->getTable('eav/entity_type')), 'a.entity_type_id = t.entity_type_id')
                ->where('entity_type_code = ?', 'magemenu')
                ->where('attribute_code = ?', 'is_active');

            $this->_isActiveAttributeId = $this->_getReadAdapter()->fetchOne($select);
        }
        return $this->_isActiveAttributeId;
    }

	
    protected function _getRestrictedAccessAttributeId()
    {
        if (is_null($this->_isRestrictedAccessAttributeId)) {
            $select = $this->_getReadAdapter()->select()
                ->from(array('a'=>$this->getTable('eav/attribute')), array('attribute_id'))
                ->join(array('t'=>$this->getTable('eav/entity_type')), 'a.entity_type_id = t.entity_type_id')
                ->where('entity_type_code = ?', 'magemenu')
                ->where('attribute_code = ?', 'group_access');

            $this->_isRestrictedAccessAttributeId = $this->_getReadAdapter()->fetchOne($select);
        }
        return $this->_isRestrictedAccessAttributeId;
    }
    public function findWhereAttributeIs($entityIdsFilter, $attribute, $expectedValue)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($attribute->getBackend()->getTable(), array('entity_id'))
            ->where('attribute_id = ?', $attribute->getId())
            ->where('value = ?', $expectedValue)
            ->where('entity_id in (?)', $entityIdsFilter);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Retrieve menus
     *
     */
    public function getMenus($parent, $recursionLevel = 0, $sorted=false, $asCollection=false, $toLoad=true)
    {
        $tree = Mage::getResourceModel('magemenu/menuitem_tree');
        $nodes = $tree->loadNode($parent)
            ->loadChildren($recursionLevel)
            ->getChildren();

        $tree->addCollectionData(null, $sorted, $parent, $toLoad, true);

        if ($asCollection) {
            return $tree->getCollection();
        }
        return $nodes;
    }

    /**
     * Return parent menus of menuitem
     *
     * @return array
     */
    public function getParentMenus($menuitem)
    {
        $pathIds = array_reverse(explode(',', $menuitem->getPathInStore()));
        $menus = Mage::getResourceModel('magemenu/menuitem_collection')
            ->setStore(Mage::app()->getStore())
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', array('in'=>$pathIds))
            ->addFieldToFilter('is_active', 1)
            ->load()
            ->getItems();
        return $menus;
    }

    /**
     *
     *
     * @param OnlineBiz_MageMenu_Model_Menuitem $menuitem
     * @return unknown
     */
    public function getChildrenMenuitems($menuitem)
    {
        $collection = $menuitem->getCollection();
        $collection->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', 1)
            ->addIdFilter($menuitem->getChildren())
            ->setOrder('position', 'ASC')
            ->joinUrlRewrite()
            ->load();
        return $collection;
    }

    /**
     * Return children ids of menuitem
     *
     * @param OnlineBiz_MageMenu_Model_Menuitem $menuitem
     * @param boolean $recursive
     * @return array
     */
    public function getChildren($menuitem, $recursive = true)
    {
		
        $activeAttributeId = $this->_getIsActiveAttributeId();
        $groupAccessAttributeId = $this->_getRestrictedAccessAttributeId();
        $select = $this->_getReadAdapter()->select()
            ->from(array('m' => $this->getEntityTable()), 'entity_id')
            ->joinLeft(
                array('d' => $this->getEntityTable() . '_int'),
                "d.attribute_id = '{$activeAttributeId}' AND d.store_id = 0 AND d.entity_id = m.entity_id",
                array()
            )
            ->joinLeft(
                array('c' => $this->getEntityTable() . '_int'),
                "c.attribute_id = '{$activeAttributeId}' AND c.store_id = '{$menuitem->getStoreId()}' AND c.entity_id = m.entity_id",
                array()
            );
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$select->joinLeft(
                array('e' => $this->getEntityTable() . '_varchar'),
                "e.attribute_id = '{$groupAccessAttributeId}' AND e.store_id = 0 AND e.entity_id = m.entity_id",
                array()
            )
			->joinLeft(
                array('f' => $this->getEntityTable() . '_varchar'),
                "f.attribute_id = '{$groupAccessAttributeId}' AND f.store_id = '{$menuitem->getStoreId()}' AND f.entity_id = m.entity_id",
                array()
            )
			->where('(IFNULL(e.value, f.value) LIKE ?)', '%'.Mage::getSingleton('customer/session')->getCustomer()->getGroupId().'%')
			;
		} else {
			$select->joinLeft(
                array('e' => $this->getEntityTable() . '_varchar'),
                "e.attribute_id = '{$groupAccessAttributeId}' AND e.store_id = 0 AND e.entity_id = m.entity_id",
                array()
            )
			->joinLeft(
                array('f' => $this->getEntityTable() . '_varchar'),
                "f.attribute_id = '{$groupAccessAttributeId}' AND f.store_id = '{$menuitem->getStoreId()}' AND f.entity_id = m.entity_id",
                array()
            )->where('(IFNULL(e.value, f.value) LIKE ?)', '%0%');
		}
        $select ->where('(IFNULL(c.value, d.value) = ?)', '1')
            ->where('path LIKE ?', "{$menuitem->getPath()}/%");
        if (!$recursive) {
            $select->where('level <= ?', $menuitem->getLevel() + 1);
        }
		
        $_menus = $this->_getReadAdapter()->fetchAll($select);
        $menusIds = array();
        foreach ($_menus as $_category) {
            $menusIds[] = $_category['entity_id'];
        }
        return $menusIds;

    }

    /**
     * Return all children ids of menuitem
     *
     * @param OnlineBiz_MageMenu_Model_Menuitem $menuitem
     * @return array
     */
    public function getAllChildren($menuitem)
    {
        $children = $this->getChildren($menuitem);
        $myId = array($menuitem->getId());
        $children = array_merge($myId, $children);

        return $children;

    }

    public function isInRootList($menu)
    {	
    	throw new Exception('Deprecated in Menu.php Line 624');
    	
    	$menuId = is_object($menu) ? $menu->getId() : $menu;
    	if( ! $menuId) {
    		return false;
    	}

        $select = $this->_getReadAdapter()->select()
        	->from($this->getEntityTable(), array("level"))
        	->where('entity_id = ? AND level = 2', $menuId);
       	
        return (bool) $this->_getReadAdapter()->fetchOne($select);
    }
    
    public function isForbiddenToDelete($menuitemId)
    {
    	return (OnlineBiz_MageMenu_Model_Menuitem::TREE_ROOT_ID === $menuitemId);
    }
    
    public function getRootIds()
    {
    	if(is_null($this->_rootIds)) {
	        $select = $this->_getReadAdapter()->select()
	            ->from($this->getTable('magemenu/menuitem'), array('entity_id'))
	            ->where('parent_id = ?', 0)
	            ->where('menu_code IS NOT NULL');
	        $this->_rootIds = (array) $this->_getReadAdapter()->fetchCol($select);
    	}

    	return $this->_rootIds;
    }
    
    /**
     * Get menuitem by code
     *
     * @param   string $code
     * @return  int|false
     */
    public function getIdByCode($code)
    {
        return $this->_read->fetchOne('select entity_id from '.$this->getEntityTable().' where menu_code=?', $code);
    }
    
    public function getCmsPageByStore($storeId = null)
    {
        
        $select = $this->_read->select();
        $select->from(array('ps' => $this->getTable('cms/page_store')), array('store_id', 'page_id'))
            ->joinLeft(array('p' => $this->getTable('cms/page')), 'p.page_id=ps.page_id', array('identifier', 'is_active', 'title'))
            ->order('store_id')
            ->order('title');

        if($storeId) {
            $select->where('store_id = ?', $storeId);
        }    
        
        return $this->_read->fetchAll($select);
    }
    
    public function getCmsBlockByStore($storeId = null)
    {
        
        $select = $this->_read->select();
        $select->from(array('ps' => $this->getTable('cms/block_store')), array('store_id', 'block_id'))
            ->joinLeft(array('p' => $this->getTable('cms/block')), 'p.block_id=ps.block_id', array('identifier', 'is_active', 'title'))
            ->order('store_id')
            ->order('title');

        if($storeId) {
            $select->where('store_id = ?', $storeId);
        }    
        
        return $this->_read->fetchAll($select);
    }
}
