<?php
/**
 * OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Edit_Node
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Edit_Node extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $form->setUseContainer(true);
        
        $menu = Mage::registry('current_menuitem');

        $fieldset = $form->addFieldset('first', array(
        	'legend'=>Mage::helper('magemenu')->__('Item settings'),
        	'class' => 'fieldset-wide'
        ));
        $this->_setFieldset($menu->getAttributes(), $fieldset);
        
        if ($menu->getId()) {
            $form->addField('entity_id', 'hidden', array(
                'name' => 'id',
            ));

            $form->setValues($menu->getData());
        }
        
        $this->setForm($form);
        return parent::_prepareForm();
	}
}