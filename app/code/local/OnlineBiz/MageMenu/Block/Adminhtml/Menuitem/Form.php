<?php
/**
 * OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Form
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        Varien_Data_Form::setElementRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_element')
        );
        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('adminhtml/catalog_form_renderer_fieldset_element')
        );        
    }
    
    protected function _getAdditionalElementTypes()
    {
        $result = array(
            'colorpicker'   => Mage::getConfig()->getBlockClassName('magemenu/adminhtml_form_element_colorpicker'),
        	'image' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_category_helper_image'),
            'price'   => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_price'),
            'boolean' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_boolean'),
            'select' => Mage::getConfig()->getBlockClassName('magemenu/adminhtml_form_element_select'),
        );

        $response = new Varien_Object();
        $response->setTypes(array());
        Mage::dispatchEvent('magemenu_adminhtml_menuitem_edit_element_types', array('response'=>$response));

        foreach ($response->getTypes() as $typeName=>$typeClass) {
        	$result[$typeName] = $typeClass;
        }

        return $result;
    }
    
}