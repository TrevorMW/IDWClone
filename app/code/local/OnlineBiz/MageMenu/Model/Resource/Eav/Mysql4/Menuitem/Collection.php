<?php
/**
 * OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Menuitem_Collection
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Menuitem_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Collection_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'magemenu_menuitem_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'menuitem_collection';

    protected $_loadWithProductCount = false;
	
    /**
     * Initialize resources
     *
     */
    protected function _construct()
    {
        $this->_init('magemenu/menuitem');
    }

    /**
     *
     *
     * @param array $menuitemIds
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection
     */
    public function addIdFilter($menuitemIds)
    {
        if (is_array($menuitemIds)) {
            if (empty($menuitemIds)) {
                $condition = '';
            } else {
                $condition = array('in' => $menuitemIds);
            }
        } elseif (is_numeric($menuitemIds)) {
            $condition = $menuitemIds;
        } elseif (is_string($menuitemIds)) {
            $ids = explode(',', $menuitemIds);
            if (empty($ids)) {
                $condition = $menuitemIds;
            } else {
                $condition = array('in' => $ids);
            }
        }
        $this->addFieldToFilter('entity_id', $condition);
        return $this;
    }

    protected function _beforeLoad()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_load_before',
                            array($this->_eventObject => $this));
        return parent::_beforeLoad();
    }

    protected function _afterLoad()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_load_after',
                            array($this->_eventObject => $this));

        return parent::_afterLoad();
    }

    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        parent::load($printQuery, $logQuery);


        return $this;
    }


    public function addPathFilter($regexp)
    {
        $this->getSelect()->where(new Zend_Db_Expr("path regexp '{$regexp}'"));
        return $this;
    }


    public function joinUrlRewrite()
    {    	

        $storeId = Mage::app()->getStore()->getId();

        $this->joinAttribute('link_to', 'magemenu/link_to', 'entity_id', null, 'left', $storeId);
        $this->joinAttribute('link_to_category', 'magemenu/link_to_category', 'entity_id', null, 'left', $storeId);        
        $this->joinAttribute('link_to_product', 'magemenu/link_to_product', 'entity_id', null, 'left', $storeId);
        $this->joinAttribute('link_to_cms_page', 'magemenu/link_to_cms_page', 'entity_id', null, 'left', $storeId);
        
        $this->joinTable(
            array('category_url' => 'core/url_rewrite'),
            'category_id=link_to_category',
            array('category_url' => 'request_path'),
            '{{table}}.is_system=1 AND {{table}}.product_id IS NULL AND {{table}}.store_id="'.$storeId.'" AND {{table}}.id_path LIKE "category/%"',
            'left'
        );
        
        $entity = Mage::getModel('eav/entity')->setType('catalog_category');
        $attribute = $entity->getAttribute('is_active');
        $this->joinTable(
            array('_category_is_active_default' => Mage::getSingleton('core/resource')->getTableName('catalog/category') . '_int'),
            'entity_id=link_to_category',
            array('value'),
            '{{table}}.attribute_id=' . $attribute->getId() . ' AND {{table}}.store_id=0',
            'left'
        );
        $this->joinTable(
            array('_category_is_active' => Mage::getSingleton('core/resource')->getTableName('catalog/category') . '_int'),
            'entity_id=link_to_category',
            array('category_is_active' => new Zend_Db_Expr('IFNULL(_category_is_active.value, _category_is_active_default.value)')),
            '{{table}}.attribute_id=' . $attribute->getId() . ' AND {{table}}.store_id='.$storeId,
            'left'
        );

        $productTable = Mage::getSingleton('core/resource')->getTableName('catalog/product');
        $this->joinTable(
            array('product' => 'catalog/product'),
            'sku=link_to_product',
            array('product_id' => 'entity_id'),
            null, 
            'left'
        );
        $this->joinTable(
            array('product_url' => 'core/url_rewrite'),
            "product_id=product_id",
            array('product_url' => 'request_path'),
            '{{table}}.is_system=1 AND {{table}}.category_id IS NULL AND {{table}}.store_id="'.$storeId.'" AND {{table}}.id_path LIKE "product/%"',
            'left'
        );

        $this->joinTable(
            array('product_enabled' => 'catalog/product_enabled_index'),
            'product_id=product_id',
            array('product_is_active' => new Zend_Db_Expr('CASE `product_enabled`.visibility WHEN ' . Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG . ' THEN 1 WHEN ' . Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH . ' THEN 1 ELSE 0 END')),
            '{{table}}.store_id="'.$storeId.'"',
            'left'
        );
        
        $this->joinTable(
            array('page_url' => 'cms/page'),
            'page_id=link_to_cms_page',
            array('cms_page_url' => 'identifier', '_cms_page_is_active' => 'is_active'),
            null,
            'left'
        );
        $this->joinTable(
            array('_page_is_active_default' => 'cms/page_store'),
            'page_id=link_to_cms_page',
            array('_page_is_active_default' => 'page_id'),
            '{{table}}.store_id = 0',
            'left'
        );
        $this->joinTable(
            array('_page_is_active' => 'cms/page_store'),
            'page_id=link_to_cms_page',
            array('_page_is_active' => 'page_id', 'cms_page_is_active' => new Zend_Db_Expr('IF(page_url.is_active AND IFNULL(_page_is_active.page_id, _page_is_active_default.page_id), 1, 0)')),
            '{{table}}.store_id = ' . $storeId,
            'left'
        );

        return $this;
    }    
    
    public function filterIsActiveAfterRewrite()
    {
    	return $this;
    	$select = $this->getSelect();
    	$select->where(
    		new Zend_Db_Expr('CASE link_to WHEN category THEN ')
    	);
    }

    /**
     * Join a table
     *
     * @param string $table
     * @param string $bind
     * @param string|array $fields
     * @param null|array $cond
     * @param string $joinType
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function joinTable($table, $bind, $fields=null, $cond=null, $joinType='inner')
    {
        if(is_array($table)) {
        	foreach($table as $tableAlias => $table) {}
        }

        if (strpos($table, '/')!==false) {
            $table = Mage::getSingleton('core/resource')->getTableName($table);
        }
        
        if( ! isset($tableAlias)) {
        	$tableAlias = $table;
        }
        
        if (!$fields) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid joined fields'));
        }
        foreach ($fields as $alias=>$field) {
            if (isset($this->_joinFields[$alias])) {
                throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Joined field with this alias (%s) is already declared', $alias));
            }
            $this->_joinFields[$alias] = array(
                'table'=>$tableAlias,
                'field'=>$field,
            );
        }

        list($pk, $fk) = explode('=', $bind);
        $bindCond = $tableAlias.'.'.$pk.'='.$this->_getAttributeFieldName($fk);

        switch ($joinType) {
            case 'left':
                $joinMethod = 'joinLeft';
                break;

            default:
                $joinMethod = 'join';
        }
        $condArr = array($bindCond);

        if (!is_null($cond)) {
            if (is_array($cond)) {
                foreach ($cond as $k=>$v) {
                    $condArr[] = $this->_getConditionSql($tableAlias.'.'.$k, $v);
                }
            } else {
                $condArr[] = str_replace('{{table}}', $tableAlias, $cond);
            }
        }
        $cond = '('.join(') AND (', $condArr).')';

        $this->getSelect()->$joinMethod(array($tableAlias=>$table), $cond, $fields);

        return $this;
    }
    
    
    protected function _getAttributeId($attribute = null)
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
    

    public function addIsActiveFilter()
    {
        $this->addAttributeToFilter('is_active', 1);
        Mage::dispatchEvent($this->_eventPrefix . '_add_is_active_filter',
                            array($this->_eventObject => $this));
        return $this;
    }

    public function addNameToResult()
    {
        $this->addAttributeToSelect('name');
        return $this;
    }

    public function addUrlRewriteToResult()
    {
        $this->joinUrlRewrite();
        return $this;
    }

    public function addPathsFilter($paths)
    {
        if (!is_array($paths)) {
            $paths = array($paths);
        }
        $select = $this->getSelect();
        $orWhere = false;
        foreach ($paths as $path) {
            if ($orWhere) {
                $select->orWhere('e.path LIKE ?', "$path%");
            } else {
                $select->where('e.path LIKE ?', "$path%");
                $orWhere = true;
            }
        }
        return $this;
    }

    public function addLevelFilter($level)
    {
        $this->getSelect()->where('e.level <= ?', $level);
        return $this;
    }

    public function addOrderField($field)
    {
        $this->setOrder($field, 'ASC');
        return $this;
    }

}
