<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem extends OnlineBiz_MageMenu_Model_Abstract
{

    const CACHE_TAG             = 'MAGEMENU_MENUITEM';

    protected $_eventPrefix     = 'magemenu_menuitem';

    protected $_eventObject     = 'menuitem';

    protected $_cacheTag        = self::CACHE_TAG;


    protected static $_url;

    protected static $_urlRewrite;

    private $_designAttributes  = array(
        'custom_design',
        'custom_design_apply',
        'custom_design_from',
        'custom_design_to',
        'page_layout',
        'custom_layout_update'
    );

    protected $_treeModel = null;

    protected $_isRoot = false;
    
    protected $_typeInstance = null;

    
    /**
     * Initialize resource mode
     *
     */
    protected function _construct()
    {
        $this->_init('magemenu/menuitem');
    }

    /**
     * Retrieve URL instance
     *
     * @return Mage_Core_Model_Url
     */
    public function getUrlInstance()
    {
        if (!self::$_url) {
            self::$_url = Mage::getModel('core/url');
        }
        return self::$_url;
    }

    /**
     * Get url rewrite model
     *
     * @return Mage_Core_Model_Url_Rewrite
     */
    public function getUrlRewrite()
    {
        if (!self::$_urlRewrite) {
            self::$_urlRewrite = Mage::getModel('core/url_rewrite');
        }
        return self::$_urlRewrite;
    }

    /**
     * Retrieve category tree model
     *

     */
    public function getTreeModel()
    {
        return Mage::getResourceModel('magemenu/menuitem_tree');
    }

    /**
     *
     *
     * @return OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Menuitem_Tree
     */
    public function getTreeModelInstance()
    {
        if (is_null($this->_treeModel)) {
            $this->_treeModel = Mage::getResourceSingleton('magemenu/menuitem_tree');
        }
        return $this->_treeModel;
    }


    /**
     * Retrieve default attribute set id
     *
     * @return int
     */
    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getEntityType()->getDefaultAttributeSetId();
    }

    public function getAttributes($noDesignAttributes = false)
    {
        $result = $this->getResource()
            ->loadAllAttributes($this)
            ->getSortedAttributes();

        if ($noDesignAttributes){
            foreach ($result as $k=>$a){
                if (in_array($k, $this->_designAttributes)) {
                    unset($result[$k]);
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve array of store ids for menuitem
     *
     * @return array
     */
    public function getStoreIds()
    {
        if ($this->getInitialSetupFlag()) {
            return array();
        }

        if ($storeIds = $this->getData('store_ids')) {
            return $storeIds;
        }
        $storeIds = $this->getResource()->getStoreIds($this);
        $this->setData('store_ids', $storeIds);
        return $storeIds;
    }

    /**
     * Retrieve Layout Update Handle name
     *
     * @return string
     */
    public function getLayoutUpdateHandle()
    {
        $layout = 'magemenu_menuitem_';
        if ($this->getIsAnchor()) {
            $layout .= 'layered';
        }
        else {
            $layout .= 'default';
        }
        return $layout;
    }

    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->_getData('store_id');
        }
        return Mage::app()->getStore()->getId();
    }

    public function setStoreId($storeId)
    {
        if (!is_numeric($storeId)) {
            $storeId = Mage::app($storeId)->getStore()->getId();
        }
        $this->setData('store_id', $storeId);
        $this->getResource()->setStoreId($storeId);
        return $this;
    }


    public function getCategoryIdUrl()
    {
        Varien_Profiler::start('REGULAR: '.__METHOD__);
        $urlKey = $this->getUrlKey() ? $this->getUrlKey() : $this->formatUrlKey($this->getName());
        $url = $this->getUrlInstance()->getUrl('magemenu/menuitem/view', array(
            's'=>$urlKey,
            'id'=>$this->getId(),
        ));
        Varien_Profiler::stop('REGULAR: '.__METHOD__);
        return $url;
    }

    public function formatUrlKey($str)
    {
        $str = Mage::helper('core')->removeAccents($str);
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $str);
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');
        return $urlKey;
    }

    public function getImageUrl()
    {
        $url = false;
        if ($image = $this->getImage()) {
            $url = Mage::getBaseUrl('media').'catalog/category/'.$image;
        }
        return $url;
    }

    public function getUrlPath()
    {
        if ($path = $this->getData('url_path')) {
            return $path;
        }

        $path = $this->getUrlKey();

        if ($this->getParentId()) {
            $parentPath = Mage::getModel('magemenu/menuitem')->load($this->getParentId())->getCategoryPath();
            $path = $parentPath.'/'.$path;
        }

        $this->setUrlPath($path);

        return $path;
    }

    /**
     * Get parent category object
     *
     * @return OnlineBiz_MageMenu_Model_Menuitem
     */
    public function getParentCategory()
    {
        return Mage::getModel('magemenu/menuitem')->load($this->getParentId());
    }

    /**
     * Get parent category identifier
     *
     * @return int
     */
    public function getParentId()
    {
        $parentIds = $this->getParentIds();
        return intval(array_pop($parentIds));
    }

    /**
     * Get all parent menus ids
     *
     * @return array
     */
    public function getParentIds()
    {
        return array_diff($this->getPathIds(), array($this->getId()));
    }

    /**
     * Retrieve dates for custom design (from & to)
     *
     * @return array
     */
    public function getCustomDesignDate()
    {
        $result = array();
        $result['from'] = $this->getData('custom_design_from');
        $result['to'] = $this->getData('custom_design_to');

        return $result;
    }

    /**
     * Retrieve attribute by code
     *
     * @param string $attributeCode
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    private function _getAttribute($attributeCode)
    {
        return $this->getResource()->getAttribute($attributeCode);
    }

    /**
     * Get all children menus IDs
     *
     * @param boolean $asArray return resul as array instead of comma-separated list of IDs
     * @return array|string
     */
    public function getAllChildren($asArray = false)
    {
        $children = $this->getResource()->getAllChildren($this);
        if ($asArray) {
            return $children;
        }
        return implode(',', $children);
    }

    /**
     * Retrieve children ids comma separated
     *
     * @return string
     */
    public function getChildren()
    {
        return implode(',', $this->getResource()->getChildren($this, false));
    }
	 /**
     * Retrieve children collection
     *
     * @return string
     */
    public function getChildrenCollection()
    {
        return $this->getResource()->getChildren($this, false);
    }
    /**
     * Retrieve Stores where isset category Path
     * Return comma separated string
     *
     * @return string
     */
    public function getPathInStore()
    {
        $result = array();
        $path = array_reverse($this->getPathIds());
        foreach ($path as $itemId) {
            if ($itemId == Mage::app()->getStore()->getRootCategoryId()) {
                break;
            }
            $result[] = $itemId;
        }
        return implode(',', $result);
    }

    public function checkMenuitemId($id)
    {
        return $this->_getResource()->checkMenuitemId($id);
    }

    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if (is_null($ids)) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }
        return $ids;
    }

    /**
     * Retrieve level
     *
     * @return int
     */
    public function getLevel()
    {
        if (!$this->hasLevel()) {
            return count(explode('/', $this->getPath())) - 1;
        }
        return $this->getData('level');
    }

    public function verifyMenuitemIds(array $ids)
    {
        return $this->getResource()->verifyMenuitemIds($ids);
    }

    /**
     * Retrieve Is Item has children flag
     *
     * @return bool
     */
    public function hasChildren()
    {
        return $this->_getResource()->getChildrenAmount($this) > 0;
    }

    /**
     * Retrieve Request Path
     *
     * @return string
     */
    public function getRequestPath()
    {
        return $this->_getData('request_path');
    }

    /**
     * Retrieve Name data wraper
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * Before delete process
     *
     * @return OnlineBiz_MageMenu_Model_Menuitem
     */
    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        if ($this->getResource()->isForbiddenToDelete($this->getId())) {
            Mage::throwException("Can't delete root category.");
        }
        return parent::_beforeDelete();
    }


    /**
     * Retrieve menus by parent
     *
     * @param int $parent
     * @param int $recursionLevel
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return mixed
     */
    public function getMenus($parent, $recursionLevel = 0, $sorted=false, $asCollection=false, $toLoad=true)
    {
        $menus = $this->getResource()
            ->getMenus($parent, $recursionLevel, $sorted, $asCollection, $toLoad);
        return $menus;
    }

    public function getNodes($parent, $recursionLevel = 0, $sorted=false, $asCollection=false, $toLoad=true)
    {
        $menus = $this->getResource()
            ->getMenus($parent, $recursionLevel, $sorted, $asCollection, $toLoad);
            
		return $menus;
    }

    /**
     * Return parent menus of current menuitem
     *
     * @return array
     */
    public function getParentMenus()
    {
        return $this->getResource()->getParentMenus($this);
    }

    /**
     * Retuen children menus of current menuitem
     *
     * @return array
     */
    public function getChildrenMenuitems()
    {
        return $this->getResource()->getChildrenMenuitems($this);
    }

    /**
     * Check is category in list of store menus
     *
     * @param OnlineBiz_MageMenu_Model_Menuitem $menuitem
     * @return boolean
     */
    public function isRoot($nodeId = null)
    {
    	if(	(int) $this->getData('level') === 1
    		|| (int) $this->getParentId() === self::TREE_ROOT_ID
    		|| $this->_isRoot ) {
    			return true;
    	}
    	return false;
    }
    
    public function setIsRoot($bool = false)
    {
    	$this->_isRoot = (bool) $bool;
    	return $this;
    }
    
    /**
     * Retrive menu id by code
     *
     * @return  integer
     */
    public function getIdByCode($code)
    {
        return $this->_getResource()->getIdByCode($code);
    }
    
    public function validate()
    {
        return $this->_getResource()->validate($this);
    }
    
    public function getTypeInstance()
    {
    	return Mage::getSingleton('magemenu/menuitem_type_' . strtolower($this->getType()))->setData($this->getData());
    	if($this->getType() && ! isset($this->_typeInstance[$this->getType()])) {
    		$this->_typeInstance[$this->getType()] = Mage::getSingleton('magemenu/menuitem_type_' . strtolower($this->getType()));
    		$this->_typeInstance[$this->getType()]->setData($this->getData());    		
    	}
    	
    	return $this->_typeInstance[$this->getType()];
    }
    
    public function getType()
    {
    	if($this->getMenuCode()) {
    		return 'root';
    	}
    	
    	if($this->getData('link_to')) {
    		return $this->getData('link_to');
    	}
    	    	
    	return 'undefined';
    }
    
    public function isActive()
    {
    	return $this->getData('is_active');
    }
    
    public function getUrl($getBaseUrl = false)
    {
    	return $this->getTypeInstance()->getUrl($getBaseUrl);
    }
    
    public function hasUrl()
    {
    	return (bool) (! is_null($this->getUrl()) && strlen($this->getUrl()) >= 1);
    }
    
    protected function _addBaseUrl($url = null)
    {
    	return $url ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . $url : null;
    }

    public function getCustomActiveDate()
    {
        $result = array();
        $result['from'] = $this->getData('active_from');
        $result['to'] = $this->getData('active_to');

        return $result;
    }
    
    
    public function getIsActive()
    {
    	$active = ($this->isActive() && $this->typeIsActive());   
    	$date = $this->getCustomActiveDate();
    	
        if (! $active || !array_key_exists('from', $date) || !array_key_exists('to', $date)) {        	
            return $active;
        }

        return (Mage::app()->getLocale()->IsStoreDateInInterval(null, $date['from'], $date['to']));
    	
    }
    
    public function typeIsActive()
    {
    	return $this->getTypeInstance()->isActive();
    }
}