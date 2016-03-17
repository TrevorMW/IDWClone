<?php
/**
 * OnlineBiz_MageMenu_Helper_Menuitem
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Helper_Menuitem extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CATEGORY_URL_SUFFIX  = 'catalog/seo/category_url_suffix';

    protected $_menuNodes = array();
    protected $_activeLinks = array();

    /**
     * Cache for category rewrite suffix
     *
     * @var array
     */
    protected $_categoryUrlSuffix = array();

    protected $_parentId = null;
    
    public function getMenuNodes($sorted=false, $asCollection=false, $toLoad=true)
    {
        $parent     = $this->getParentId();
        $cacheKey   = sprintf('%d-%d-%d-%d', $parent, $sorted, $asCollection, $toLoad);
        if (isset($this->_menuNodes[$cacheKey])) {
            return $this->_menuNodes[$cacheKey];
        }

        /**
         * Check if parent node of the store still exists
         */
        $menu = Mage::getModel('magemenu/menuitem');
        /* @var $menu OnlineBiz_MageMenu_Model_Menuitem */
        if (!$menu->checkMenuitemId($parent)) {
            if ($asCollection) {
                return new Varien_Data_Collection();
            }
            return array();
        }
        $recursionLevel  = 6;
        $menuNodes = $menu->getNodes($parent, $recursionLevel, $sorted, $asCollection, $toLoad);
        $this->_menuNodes[$cacheKey] = $menuNodes;
        return $menuNodes;
    }

    /**
     * Retrieve menuitem url
     *
     * @param   OnlineBiz_MageMenu_Model_Menuitem $menu
     * @return  string
     */
    public function getMenuitemUrl($menu)
    {
        if ($menu instanceof OnlineBiz_MageMenu_Model_Menuitem) {
            return $menu->getUrl();
        }
        return Mage::getModel('catalog/category')
            ->setData($menu->getData())
            ->getUrl();
    }

    /**
     * Check if a menuitem can be shown
     *
     * @param  OnlineBiz_MageMenu_Model_Menuitem|int $menu
     * @return boolean
     */
    public function canShow($menu)
    {
        if (is_int($menu)) {
            $menu = Mage::getModel('catalog/category')->load($menu);
        }

        if (!$menu->getId()) {
            return false;
        }

        if (!$menu->getIsActive()) {
            return false;
        }
        if (!$menu->isInRootCategoryList()) {
            return false;
        }

        return true;
    }

	/**
     * Retrieve category rewrite sufix for store
     *
     * @param int $storeId
     * @return string
     */
    public function getMenuitemUrlSuffix($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!isset($this->_categoryUrlSuffix[$storeId])) {
            $this->_categoryUrlSuffix[$storeId] = Mage::getStoreConfig(self::XML_PATH_CATEGORY_URL_SUFFIX, $storeId);
        }
        return $this->_categoryUrlSuffix[$storeId];
    }

    /**
     * Retrieve clear url for category as parrent
     *
     * @param string $url
     * @param bool $slash
     * @param int $storeId
     *
     * @return string
     */
    public function getMenuitemUrlPath($urlPath, $slash = false, $storeId = null)
    {
        if (!$this->getMenuitemUrlSuffix($storeId)) {
            return $urlPath;
        }

        if ($slash) {
            $regexp     = '#('.preg_quote($this->getMenuitemUrlSuffix($storeId), '#').')/$#i';
            $replace    = '/';
        }
        else {
            $regexp     = '#('.preg_quote($this->getMenuitemUrlSuffix($storeId), '#').')$#i';
            $replace    = '';
        }

        return preg_replace($regexp, $replace, $urlPath);
    }
    
    public function setParentId($id)
    {
    	$this->_parentId = (int) $id;
    	return $this;
    }
    
    public function getParentId()
    {
    	return $this->_parentId;
    }
}
