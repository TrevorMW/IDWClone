<?php
/**
 * OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Edit_Form
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Edit_Form 
	extends OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Abstract
{
    /**
     * Additional buttons on category page
     *
     * @var array
     */
    protected $_additionalButtons = array();

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('onlinebizsoft/magemenu/edit/form.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('tabs',
            $this->getLayout()->createBlock('magemenu/adminhtml_menuitem_edit_tabs', 'tabs')
        );
/*
        $this->setChild('node',
            $this->getLayout()->createBlock('magemenu/adminhtml_menuitem_edit_node', 'node')
        );
*/	
		$addUrl = $this->getUrl("*/*/add", array(
            '_current'=>true,
            'id'=>null,
            '_query' => false
        ));
		$this->setChild('add_root_button',
			$this->getLayout()->createBlock('adminhtml/widget_button')
				->setData(array(
					'label'     => Mage::helper('magemenu')->__('Add Menu'),
					'onclick'   => "addNew('".$addUrl."', true)",
					'class'     => 'add',
					'id'        => 'add_root_node_button'
				))
		);
        if (!$this->getMenu()->isReadonly()) {
            $this->setChild('save_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('magemenu')->__($this->getMenu()->isRoot() ? 'Save Menu' : 'Save Menu Item'),
                        'onclick'   => "menuNodeSubmit('".$this->getSaveUrl()."',true)",
                        'class' => 'save'
                    ))
            );
        }
        if (!in_array($this->getMenu()->getId(), $this->getRootIds()) &&
            $this->getMenu()->isDeleteable()) {
            $this->setChild('delete_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('magemenu')->__($this->getMenu()->isRoot() ? 'Delete Menu' : 'Delete Menu Item'),
                        'onclick'   => "menuNodeDelete('".$this->getUrl('*/*/delete', array('_current'=>true))."',true)",
                        'class' => 'delete'
                    ))
            );
        }

        if (!$this->getMenu()->isReadonly()) {
            $this->setChild('reset_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('magemenu')->__('Reset'),
                        'onclick'   => "menuNodeReset('".$this->getUrl('*/*/edit', array('_current'=>true))."',true)"
                    ))
            );
        }

        return parent::_prepareLayout();
    }

    public function getStoreConfigurationUrl()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $params = array();
//        $params = array('section'=>'magemenu');
        if ($storeId) {
            $store = Mage::app()->getStore($storeId);
            $params['website'] = $store->getWebsite()->getCode();
            $params['store']   = $store->getCode();
        }
        return $this->getUrl('*/system_store', $params);
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getSaveButtonHtml()
    {
        if ($this->hasRootMenu()) {
            return $this->getChildHtml('save_button');
        }
        return '';
    }
	public function getAddButtonHtml()
    {
        if ($this->hasRootMenu()) {
            return $this->getChildHtml('add_root_button');
        }
        return '';
    }
    public function getResetButtonHtml()
    {
        if ($this->hasRootMenu()) {
            return $this->getChildHtml('reset_button');
        }
        return '';
    }

    /**
     * Retrieve additional buttons html
     *
     * @return string
     */
    public function getAdditionalButtonsHtml()
    {
        $html = '';
        foreach ($this->_additionalButtons as $childName) {
            $html .= $this->getChildHtml($childName);
        }
        return $html;
    }

    /**
     * Add additional button
     *
     * @param string $alias
     * @param array $config
     * @return Mage_Adminhtml_Block_Catalog_Category_Edit_Form
     */
    public function addAdditionalButton($alias, $config)
    {
        $this->setChild($alias . '_button',
                        $this->getLayout()->createBlock('adminhtml/widget_button')->addData($config));
        $this->_additionalButtons[$alias] = $alias . '_button';
        return $this;
    }

    /**
     * Remove additional button
     *
     * @param string $alias
     * @return Mage_Adminhtml_Block_Catalog_Category_Edit_Form
     */
    public function removeAdditionalButton($alias)
    {
        if (isset($this->_additionalButtons[$alias])) {
            $this->unsetChild($this->_additionalButtons[$alias]);
            unset($this->_additionalButtons[$alias]);
        }

        return $this;
    }

    public function getTabsHtml()
    {
        return $this->getChildHtml('tabs');
    }

    public function getHeader()
    {
        if ($this->hasRootMenu()) {
        	if($this->getMenuId()) {
        		return $this->getMenuName();
        	} else {
        		return Mage::helper('magemenu')->__($this->getMenu()->isRoot() ? 'New Menu' : 'New Menu Item');
        	}
        }
        return Mage::helper('magemenu')->__('Set Root Item For Store');
    }

    public function getDeleteUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/delete', $params);
    }

    public function isAjax()
    {
        return Mage::app()->getRequest()->isXmlHttpRequest() || Mage::app()->getRequest()->getParam('isAjax');
    }
}
