<?php
/**
 * OnlineBiz_MageMenu_Adminhtml_MenuitemController
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Adminhtml_MenuitemController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        $this->setUsedModuleName('OnlineBiz_MageMenu');
    }

    protected function _initMenu()
    {
    	if(null === Mage::registry('current_menuitem')) {
    		$treeRootId = OnlineBiz_MageMenu_Model_Menuitem::TREE_ROOT_ID;
	        $menuId  	= (int) $this->getRequest()->getParam('id', false);
	        $isRoot 	= (int) $this->getRequest()->getParam('root');
	        $menu		= Mage::getModel('magemenu/menuitem')
	            	 	->setStoreId($this->getRequest()->getParam('store', 0));
	            
	        if ($menuId) {
	            $menu->load($menuId);
	        } 
	        
	        if( ! $menuId && $isRoot) {
	        	$menu->setIsRoot(true);
	        	$menu->setParentId($treeRootId);
	        	$this->getRequest()->setParam('parent', $treeRootId);
	        }
	        
	        if ($activeTabId = (string) $this->getRequest()->getParam('active_tab_id')) {
	            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
	        }
	
	        Mage::register('current_menuitem', $menu);
    	}
    	
        return Mage::registry('current_menuitem');
    }
    
    
    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('magemenu/managemenu');
    }
	
    public function indexAction()
    {
    	$this->_forward('edit');
    }
    

    public function addAction()
    {
        $this->_forward('edit');
    }
    
    public function newAction()
    {
    	$this->_forward('edit');
	
    }

    public function editAction()
    {
    	$params['_current'] = true;
        $redirect = false;

        $storeId = (int) $this->getRequest()->getParam('store');
        $parentId = (int) $this->getRequest()->getParam('parent');        
        $_prevStoreId = Mage::getSingleton('admin/session')
            ->getLastViewedStore(true);

        if ($_prevStoreId != null && !$this->getRequest()->getQuery('isAjax')) {
            $params['store'] = $_prevStoreId;
            $redirect = true;
        }

        $menuId = (int) $this->getRequest()->getParam('id');
        $_prevCategoryId = Mage::getSingleton('admin/session')
            ->getLastEditedCategory(true);


        if ($_prevCategoryId
            && !$this->getRequest()->getQuery('isAjax')
            && !$this->getRequest()->getParam('clear')) {
            $this->getRequest()->setParam('id',$_prevCategoryId);
        }

         if ($redirect) {
            $this->_redirect('*/*/edit', $params);
            return;
        }

        if ($storeId && !$menuId && !$parentId) {
            $store = Mage::app()->getStore($storeId);
            $_prevCategoryId = OnlineBiz_MageMenu_Model_Menuitem::TREE_ROOT_ID;
            $this->getRequest()->setParam('id', $_prevCategoryId);
        }

        if (!($menu = $this->_initMenu())) {
            return;
        }        

        $data = Mage::getSingleton('adminhtml/session')->getMenuData(true);
        if (isset($data['general'])) {
            $menu->addData($data['general']);
        }
		

        if ($this->getRequest()->getQuery('isAjax')) {
       	
	        $typeInstance = $menu->getTypeInstance();
	        $typeInstance->checkForWarnings();
	        if($typeInstance->hasWarnings()) {
	        	foreach($typeInstance->getWarnings() as $warning) {
	        		$this->_getSession()->addWarning($warning);
	        	}
	        }

            $breadcrumbsPath = $menu->getPath();
            if (empty($breadcrumbsPath)) {

                $breadcrumbsPath = Mage::getSingleton('admin/session')->getDeletedPath(true);
                if (!empty($breadcrumbsPath)) {
                    $breadcrumbsPath = explode('/', $breadcrumbsPath);

                    if (count($breadcrumbsPath) <= 1) {
                        $breadcrumbsPath = '';
                    }
                    else {
                        array_pop($breadcrumbsPath);
                        $breadcrumbsPath = implode('/', $breadcrumbsPath);
                    }
                }
            }         
                    
            Mage::getSingleton('admin/session')
                ->setLastViewedStore($this->getRequest()->getParam('store'));
            Mage::getSingleton('admin/session')
                ->setLastEditedCategory($menu->getId());
            $this->loadLayout();
            
            $this->getResponse()->setBody(
                $this->getLayout()->getMessagesBlock()->getGroupedHtml()
                . $this->getLayout()->getBlock('menu.edit')->getFormHtml()
                . $this->getLayout()->getBlock('menu.tree')
                    ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingCategoryBreadcrumbs')
            );
            
            return;
        }
		
        $this->loadLayout();
        $this->_setActiveMenu('magemenu');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
            ->setContainerCssClass('catalog-menus');

        $this->_addBreadcrumb(Mage::helper('magemenu')->__('Manage Menu Nodes'),
             Mage::helper('magemenu')->__('Manage Menu Nodes')
        );
        $this->renderLayout();
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('magemenu/adminhtml_menuitem_grid')->toHtml()
        );
    }

    public function saveAction()
    {
        if (!$menu = $this->_initMenu()) {
            return;
        }

        $storeId = $this->getRequest()->getParam('store');
        $refreshTree = 'false';
        $isNewNode = false;
        if ($data = $this->getRequest()->getPost()) {
            $menu->addData($data['general']);
            if (!$menu->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId || isset($data['general']['_is_root'])) {
	                $parentId = OnlineBiz_MageMenu_Model_Menuitem::TREE_ROOT_ID;
                }
                $parentCategory = Mage::getModel('magemenu/menuitem')->load($parentId);
                $menu->setPath($parentCategory->getPath());
            }

            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $menu->setData($attributeCode, null);
                }
            }

            $menu->setAttributeSetId($menu->getDefaultAttributeSetId());

            Mage::dispatchEvent('magemenu_menuitem_prepare_save', array(
                'menu' => $menu,
                'request' => $this->getRequest()
            ));

            try {
           		$isNewNode = ( ! $menu->getId());
            	$nodeType = $menu->getTypeInstance();
            	if($nodeType->isValid()) {            		
                	$menu->save(); 
                	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magemenu')->__($menu->isRoot() ? 'Menu saved' : 'Item saved'));
                	$refreshTree = 'true';
            	} else {
            		foreach($nodeType->getErrors() as $error) {
            			$this->_getSession()->addError($error);
            		}
                	$this->_getSession()->setMenuData($data);
                	$refreshTree = 'false';
            	}
            }
            catch (Exception $e){
                $this->_getSession()->addError($e->getMessage())
                    ->setMenuData($data);
                $refreshTree = 'false';
            }
		}
        
		$url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $menu->getId()));        
		$this->getResponse()->setBody(
			'<script type="text/javascript">parent.updateContent("' . $url . '", {}, '.$refreshTree.');</script>'
		);
        return;
        
        
        if($isNewNode && version_compare(Mage::getVersion(), '1.6.0.0', '>=')) {
	        $this->getResponse()->setBody(
	            '<script type="text/javascript">parent.reloadFullTree("'. $menu->getId() . '", "'. $storeId . '");</script>'
	        );
        } else {
        	$url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $menu->getId()));        
	        $this->getResponse()->setBody(
	            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, '.$refreshTree.');</script>'
	        );
        }
    }
    
    public function nodesJsonAction()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(false);
        }
        if ($id = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $id);

            if (!$menu = $this->_initMenu()) {
                return;
            }
            
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('magemenu/adminhtml_menuitem_edit_tree')
                    ->getTreeJson($menu)
            );
        }
    }

    public function treeAction()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $id = (int) $this->getRequest()->getParam('id');
        $menu = $this->_initMenu();
		if( ! $storeId && $menu) {
			$this->getRequest()->setParam('id', $menu->getId());
		}

        $block = $this->getLayout()->createBlock('magemenu/adminhtml_menuitem_edit_tree');
        $root  = $block->getRoot();
        $this->getResponse()->setBody(Zend_Json::encode(array(
            'data' => $block->getTree(),
            'parameters' => array(
                'text'       	=> $block->buildNodeName($root),
                'draggable'   	=> false,
                'allowDrop'   	=> ($root->getIsVisible()) ? true : false,
                'id'          	=> (int) $root->getId(),
                'expanded'    	=> (int) $block->getIsWasExpanded(),
                'store_id'    	=> (int) $block->getStore()->getId(),
                'node_id' 		=> (int) $menu->getId(),
                'root_visible'	=> (int) $root->getIsVisible()
        ))));
    }

    public function moveAction()
    {
        $nodeId           = $this->getRequest()->getPost('id', false);
        $parentNodeId     = $this->getRequest()->getPost('pid', false);
        $prevNodeId       = $this->getRequest()->getPost('aid', false);
        $prevParentNodeId = $this->getRequest()->getPost('paid', false);

        try {
            $tree = Mage::getResourceModel('magemenu/menuitem_tree')
                ->load();

            $node = $tree->getNodeById($nodeId);
            $newParentNode  = $tree->getNodeById($parentNodeId);
            $prevNode       = $tree->getNodeById($prevNodeId);

            if (!$prevNode || !$prevNode->getId()) {
                $prevNode = null;
            }

            $tree->move($node, $newParentNode, $prevNode);

            Mage::dispatchEvent('menu_node_move',
                array(
                    'category_id' => $nodeId,
                    'prev_parent_id' => $prevParentNodeId,
                    'parent_id' => $parentNodeId
            ));

            $this->getResponse()->setBody("SUCCESS");
        }
        catch (Mage_Core_Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }
        catch (Exception $e){
            $this->getResponse()->setBody(Mage::helper('magemenu')->__('Item move error'));
        }

    }

    public function deleteAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            try {
                $menu = Mage::getModel('magemenu/menuitem')->load($id);
                Mage::dispatchEvent('magemenu_controller_menuitem_node_delete', array('node'=>$menu));

                Mage::getSingleton('admin/session')->setDeletedPath($menu->getPath());

                $menu->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magemenu')->__('Item deleted'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magemenu')->__('Item delete error'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('_current'=>true, 'id'=>null)));
    }
    
}