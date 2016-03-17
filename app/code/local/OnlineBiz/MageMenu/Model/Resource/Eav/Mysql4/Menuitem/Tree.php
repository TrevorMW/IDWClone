<?php
/**
 * OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Menuitem_Tree
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Menuitem_Tree extends Varien_Data_Tree_Dbp
{

	const CACHE_TAG = 'magemenu_menuitem_tree';
	

    protected $_collection;

    protected $_isActiveAttributeId = null;
    protected $_isRestrictedAccessAttributeId = null;

    protected $_joinUrlRewriteIntoCollection = false;

    /**
     * Inactive menus ids
     *
     * @var array
     */
    protected $_inactiveMenuitemIds = null;

    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');

        parent::__construct(
            $resource->getConnection('magemenu_read'),
            $resource->getTableName('magemenu/menuitem'),
            array(
                Varien_Data_Tree_Dbp::ID_FIELD       => 'entity_id',
                Varien_Data_Tree_Dbp::PATH_FIELD     => 'path',
                Varien_Data_Tree_Dbp::ORDER_FIELD    => 'position',
                Varien_Data_Tree_Dbp::LEVEL_FIELD    => 'level',
            )
        );
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = (int) $storeId;
        return $this;
    }

    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->_storeId = Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }


    public function addCollectionData($collection=null, $sorted=false, $exclude=array(), $toLoad=true, $onlyActive = false)
    {
        if (is_null($collection)) {
            $collection = $this->getCollection($sorted);
        } else {
            $this->setCollection($collection);
        }

        if (!is_array($exclude)) {
            $exclude = array($exclude);
        }

        if(version_compare(Mage::getVersion(), '1.4.1.0', '<')) {
	        $collection->initCache(
	            Mage::app()->getCache(),
	            self::CACHE_TAG,
	            array(
	        	    OnlineBiz_MageMenu_Model_Menuitem::CACHE_TAG,
	        	    Mage_Catalog_Model_Product::CACHE_TAG,
	        	    OnlineBiz_MageMenu_Model_Menuitem::CACHE_TAG,
	        	    Mage_Cms_Model_Block::CACHE_TAG, 
	                OnlineBiz_MageMenu_Model_Menuitem::CACHE_TAG, 
	                Mage_Core_Model_Store_Group::CACHE_TAG
	            )
	        );
        }
                
        $nodeIds = array();
        foreach ($this->getNodes() as $node) {
            if (!in_array($node->getId(), $exclude)) {
                $nodeIds[] = $node->getId();
            }
        }
        
        $collection->addAttributeToSelect('*');

        $collection->addIdFilter($nodeIds);
        if ($onlyActive) {

            $disabledIds = $this->_getDisabledIds($collection);
            if ($disabledIds) {
                $collection->addFieldToFilter('entity_id', array('nin'=>$disabledIds));
            }
            
            $collection->addAttributeToFilter('is_active', 1);

        }
        
		
        if ($this->_joinUrlRewriteIntoCollection) {
        	$collection->joinUrlRewrite();
            $this->_joinUrlRewriteIntoCollection = false;
        }
        

        if($toLoad) {
            $collection->load();

            foreach ($collection as $menuitem) {
                if ($this->getNodeById($menuitem->getId())) {
                    $this->getNodeById($menuitem->getId())
                        ->addData($menuitem->getData());
                }
            }


            foreach ($this->getNodes() as $node) {
                if (!$collection->getItemById($node->getId()) && $node->getParent()) {
                    $this->removeNode($node);
                }
            }
        }

        return $this;
    }

    protected function _initInactiveMenuitemIds()
    {
        $this->_inactiveMenuitemIds = array();
        Mage::dispatchEvent('magemenu_menuitem_tree_init_inactive_menuitem_ids', array('tree'=>$this));
        return $this;
    }

    /**
     * Retreive inactive menus ids
     *
     * @return array
     */
    public function getInactiveMenuitemIds()
    {
        if (!is_array($this->_inactiveMenuitemIds)) {
            $this->_initInactiveMenuitemIds();
        }

        return $this->_inactiveMenuitemIds;
    }

    protected function _getDisabledIds($collection)
    {
        $storeId = Mage::app()->getStore()->getId();

        $this->_inactiveItems = $this->getInactiveMenuitemIds();


        $this->_inactiveItems = array_merge(
            $this->_getInactiveItemIds($collection, $storeId),
            $this->_inactiveItems
        );
		$this->_inactiveItems = array_merge(
            $this->_getRestrictedAccessItemIds($collection, $storeId),
            $this->_inactiveItems
        );

        $allIds = $collection->getAllIds();
        $disabledIds = array();

        foreach ($allIds as $id) {
            $parents = $this->getNodeById($id)->getPath();
            foreach ($parents as $parent) {
                if (!$this->_getItemIsActive($parent->getId(), $storeId)){
                    $disabledIds[] = $id;
                    continue;
                }
            }
        }
        return $disabledIds;
    }

    protected function _getIsActiveAttributeId()
    {
        if (is_null($this->_isActiveAttributeId)) {
            $select = $this->_conn->select()
                ->from(array('a'=>Mage::getSingleton('core/resource')->getTableName('eav/attribute')), array('attribute_id'))
                ->join(array('t'=>Mage::getSingleton('core/resource')->getTableName('eav/entity_type')), 'a.entity_type_id = t.entity_type_id')
                ->where('entity_type_code = ?', 'magemenu')
                ->where('attribute_code = ?', 'is_active');

            $this->_isActiveAttributeId = $this->_conn->fetchOne($select);
        }
        return $this->_isActiveAttributeId;
    }
	
    protected function _getRestrictedAccessAttributeId()
    {
        if (is_null($this->_isRestrictedAccessAttributeId)) {
            $select = $this->_conn->select()
                ->from(array('a'=>Mage::getSingleton('core/resource')->getTableName('eav/attribute')), array('attribute_id'))
                ->join(array('t'=>Mage::getSingleton('core/resource')->getTableName('eav/entity_type')), 'a.entity_type_id = t.entity_type_id')
                ->where('entity_type_code = ?', 'magemenu')
                ->where('attribute_code = ?', 'group_access');

            $this->_isRestrictedAccessAttributeId = $this->_conn->fetchOne($select);
        }
        return $this->_isRestrictedAccessAttributeId;
    }
	
    protected function _getInactiveItemIds($collection, $storeId)
    {
        $filter = $collection->getAllIdsSql();
        $attributeId = $this->_getIsActiveAttributeId();

        $table = Mage::getSingleton('core/resource')->getTableName('magemenu/menuitem') . '_int';
        $select = $this->_conn->select()
            ->from(array('d'=>$table), array('d.entity_id'))
            ->where('d.attribute_id = ?', $attributeId)
            ->where('d.store_id = ?', 0)
            ->where('d.entity_id IN (?)', new Zend_Db_Expr($filter))
            ->joinLeft(array('c'=>$table), "c.attribute_id = '{$attributeId}' AND c.store_id = '{$storeId}' AND c.entity_id = d.entity_id", array())
            ->where('IFNULL(c.value, d.value) = ?', 0);

        return $this->_conn->fetchCol($select);
    }
	
	protected function _getRestrictedAccessItemIds($collection, $storeId)
    {
        $filter = $collection->getAllIdsSql();
        $attributeId = $this->_getRestrictedAccessAttributeId();
		
        $varTable = Mage::getSingleton('core/resource')->getTableName('magemenu/menuitem') . '_varchar';
        $select = $this->_conn->select()
            ->from(array('d'=>$varTable), array('d.entity_id'))
            ->where('d.attribute_id = ?', $attributeId)
            ->where('d.store_id = ?', 0)
            ->where('d.entity_id IN (?)', new Zend_Db_Expr($filter))
            ->joinLeft(array('c'=>$varTable), "c.attribute_id = '{$attributeId}' AND c.store_id = '{$storeId}' AND c.entity_id = d.entity_id", array());
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$select->where('(IFNULL(c.value, d.value) NOT LIKE ?)', '%'.Mage::getSingleton('customer/session')->getCustomer()->getGroupId().'%')
			;
		} else {
			$select->where('(IFNULL(c.value, d.value) NOT LIKE ?)', '%0%');
		}

        return $this->_conn->fetchCol($select);
    }

    protected function _getItemIsActive($id)
    {
        if (!in_array($id, $this->_inactiveItems)) {
            return true;
        }
        return false;
    }


    public function getCollection($sorted=false)
    {
        if (is_null($this->_collection)) {
            $this->_collection = $this->_getDefaultCollection($sorted);
        }
        return $this->_collection;
    }

    public function setCollection($collection)
    {
        if (!is_null($this->_collection)) {
            destruct($this->_collection);
        }
        $this->_collection = $collection;
        return $this;
    }


    protected function _getDefaultCollection($sorted=false)
    {
        $this->_joinUrlRewriteIntoCollection = true;
        $collection = Mage::getModel('magemenu/menuitem')->getCollection();

        $collection->addAttributeToSelect('*');


        if ($sorted) {
            if (is_string($sorted)) {
                $collection->addAttributeToSort($sorted);
            } else {
                $collection->addAttributeToSort('name');
            }
        }
        return $collection;
     }


    /**
     * Move tree before
     *
     */
    protected function _beforeMove($menuitem, $newParent, $prevNode)
    {
        Mage::dispatchEvent('magemenu_menuitem_tree_move_before',
            array(
                'category' => $menuitem,
                'prev_parent' => $prevNode,
                'parent' => $newParent
        ));

        return $this;
    }


    public function move($menuitem, $newParent, $prevNode = null) {

        $this->_beforeMove($menuitem, $newParent, $prevNode);
        Mage::getResourceSingleton('magemenu/menuitem')->move($menuitem->getId(), $newParent->getId());
        parent::move($menuitem, $newParent, $prevNode);

        $this->_afterMove($menuitem, $newParent, $prevNode);
    }

        /**
     * Move tree after
     *
     */
    protected function _afterMove($menuitem, $newParent, $prevNode)
    {
        Mage::app()->cleanCache(array(OnlineBiz_MageMenu_Model_Menuitem::CACHE_TAG));

        Mage::dispatchEvent('magemenu_menuitem_tree_move_after',
            array(
                'category' => $menuitem,
                'prev_node' => $prevNode,
                'parent' => $newParent
        ));

        return $this;
    }


    public function loadByIds($ids, $addCollectionData = true, $updateAnchorProductCount = true)
    {
        if (empty($ids)) {
            $select = $this->_conn->select()
                ->from($this->_table, 'entity_id')
                ->where('`level` <= 2');
            $ids = $this->_conn->fetchCol($select);
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        foreach ($ids as $key => $id) {
            $ids[$key] = (int)$id;
        }

        $select = $this->_conn->select()
            ->from($this->_table, array('path', 'level'))
            ->where(sprintf('entity_id IN (%s)', implode(', ', $ids)));
        $where = array('`level`=0' => true);
        foreach ($this->_conn->fetchAll($select) as $item) {
            $path  = explode('/', $item['path']);
            $level = (int)$item['level'];
            while ($level > 0) {
                $path[count($path) - 1] = '%';
                $where[sprintf("`level`=%d AND `path` LIKE '%s'", $level, implode('/', $path))] = true;
                array_pop($path);
                $level--;
            }
        }
        $where = array_keys($where);

        if ($addCollectionData) {
            $select = $this->_createCollectionDataSelect();
        }
        else {
            $select = clone $this->_select;
            $select->order($this->_orderField . ' ASC');
        }
        $select->where(implode(' OR ', $where));

        $arrNodes = $this->_conn->fetchAll($select);
        if (!$arrNodes) {
            return false;
        }
        if ($updateAnchorProductCount) {
            $this->_updateAnchorProductCount($arrNodes);
        }
        $childrenItems = array();
        foreach ($arrNodes as $key => $nodeInfo) {
            $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
            array_pop($pathToParent);
            $pathToParent = implode('/', $pathToParent);
            $childrenItems[$pathToParent][] = $nodeInfo;
        }
        $this->addChildNodes($childrenItems, '', null);

        return $this;
    }

    public function loadBreadcrumbsArray($path, $addCollectionData = true, $withRootNode = false)
    {
        $path = explode('/', $path);
        if (!$withRootNode) {
            array_shift($path);
        }
        $result = array();
        if (!empty($path)) {
            if ($addCollectionData) {
                $select = $this->_createCollectionDataSelect(false);
            }
            else {
                $select = clone $this->_select;
            }
            $select->where(sprintf('e.entity_id IN (%s)', implode(', ', $path)))
                ->order(new Zend_Db_Expr('LENGTH(e.path) ASC'));
            $result = $this->_conn->fetchAll($select);
        }
        return $result;
    }

    /**
     * Replace products count with self products count, if category is non-anchor
     *
     * @param array $data
     */
    protected function _updateAnchorProductCount(&$data)
    {
        foreach ($data as $key => $row) {
            if (0 === (int)$row['is_anchor']) {
                $data[$key]['product_count'] = $row['self_product_count'];
            }
        }
    }

    protected function _createCollectionDataSelectOld($sorted = true, $optionalAttributes = array())
    {
        $select = $this->_getDefaultCollection($sorted ? $this->_orderField : false)
            ->getSelect();
        $attributes = array('name', 'is_active', 'link_to');
        if ($optionalAttributes) {
            $attributes = array_unique(array_merge($attributes, $optionalAttributes));
        }
        foreach ($attributes as $attributeCode) {
            $attribute = Mage::getResourceSingleton('magemenu/menuitem')->getAttribute($attributeCode);
            if (!$attribute->getBackend()->isStatic()) {
                $tableAs   = "_$attributeCode";
                $select->joinLeft(
                    array($tableAs => $attribute->getBackend()->getTable()),
                    sprintf('`%1$s`.entity_id=e.entity_id AND `%1$s`.attribute_id=%2$d AND `%1$s`.entity_type_id=e.entity_type_id AND `%1$s`.store_id=%3$d',
                        $tableAs, $attribute->getData('attribute_id'), Mage_Core_Model_App::ADMIN_STORE_ID
                    ),
                    array($attributeCode => 'value')
                );
            }
        }
		return $select;    	
    }    
    
    protected function _createCollectionDataSelect($sorted = true, $optionalAttributes = array())
    {
    	if(version_compare(Mage::getVersion(), '1.6.0.0', '<')) {
    		return $this->_createCollectionDataSelectOld($sorted, $optionalAttributes);
    	}
    	
        $select = $this->_getDefaultCollection($sorted ? $this->_orderField : false)
            ->getSelect();
        $attributes = array('name', 'is_active', 'link_to');
        if ($optionalAttributes) {
            $attributes = array_unique(array_merge($attributes, $optionalAttributes));
        }
        foreach ($attributes as $attributeCode) {
            $attribute = Mage::getResourceSingleton('magemenu/menuitem')->getAttribute($attributeCode);
            if (!$attribute->getBackend()->isStatic()) {
                $tableAs   = "_$attributeCode";
                $tableDefault   = sprintf('default_%s', $attributeCode);
                $tableStore     = sprintf('store_%s', $attributeCode);
                $valueExpr      = $this->_conn
                    ->getCheckSql("{$tableStore}.value_id > 0", "{$tableStore}.value", "{$tableDefault}.value");
                
                $select
                    ->joinLeft(
                        array($tableDefault => $attribute->getBackend()->getTable()),
                        sprintf('%1$s.entity_id=e.entity_id AND %1$s.attribute_id=%2$d'
                            . ' AND %1$s.entity_type_id=e.entity_type_id AND %1$s.store_id=%3$d',
                            $tableDefault, $attribute->getId(), Mage_Core_Model_App::ADMIN_STORE_ID),
                        array($attributeCode => 'value'))
                    ->joinLeft(
                        array($tableStore => $attribute->getBackend()->getTable()),
                        sprintf('%1$s.entity_id=e.entity_id AND %1$s.attribute_id=%2$d'
                            . ' AND %1$s.entity_type_id=e.entity_type_id AND %1$s.store_id=%3$d',
                            $tableStore, $attribute->getId(), $this->getStoreId()),
                        array($attributeCode => $valueExpr)
                    );
            }
        }

		return $select;
    }
}
