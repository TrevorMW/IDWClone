<?php
/**
 * OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Edit
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'entity_id';
        $this->_blockGroup	= 'magemenu';
        $this->_controller  = 'adminhtml_menuitem';
        $this->_mode        = 'edit';
	
        parent::__construct();
        $this->setTemplate('onlinebizsoft/magemenu/edit.phtml');
    }
}
