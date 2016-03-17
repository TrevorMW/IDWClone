<?php
/**
 * OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Edit_Tab_Attributes
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Edit_Tab_Attributes 
	extends OnlineBiz_MageMenu_Block_Adminhtml_Menuitem_Form
{

    public function __construct() {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    protected function _prepareForm() {
        $group      = $this->getGroup();
        $attributes = $this->getAttributes();

        $menu = Mage::registry('current_menuitem');
        
        $form = new Varien_Data_Form();
        $form->setDataObject($menu);

        $fieldset = $form->addFieldset('fieldset_group_' . $group->getId(), array(
            'legend'    => Mage::helper('magemenu')->__($group->getAttributeGroupName())
        ));
        if ($this->getAddHiddenFields()) {
            if (!$menu->getId()) {
                // path
                if ($this->getRequest()->getParam('parent')) {
                    $fieldset->addField('path', 'hidden', array(
                        'name'  => 'path',
                        'value' => $this->getRequest()->getParam('parent')
                    ));
                }
                else {
                    $fieldset->addField('path', 'hidden', array(
                        'name'  => 'path',
                        'value' => 1
                    ));
                }
            }
            else {
                $fieldset->addField('id', 'hidden', array(
                    'name'  => 'id',
                    'value' => $menu->getId()
                ));
                $fieldset->addField('path', 'hidden', array(
                    'name'  => 'path',
                    'value' => $menu->getPath()
                ));
            }
        }

        $this->_setFieldset($attributes, $fieldset);
        
        if($menu->getMenuCode()) {
        	$menu->lockAttribute('menu_code');
        	if($element = $form->getElement('image')) {
        		$element->setLabel('Background Image');
        	}
        }
        
        if ($menu->hasLockedAttributes()) {
            foreach ($menu->getLockedAttributes() as $attribute) {
                if ($element = $form->getElement($attribute)) {
                    $element->setReadonly(true, true);
                }
            }
        }

        $form->addValues($menu->getData());
        $form->setFieldNameSuffix('general');
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Set Fieldset to Form
     *
     * @param array $attributes attributes that are to be added
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $exclude attributes that should be skipped
     */
    protected function _setFieldset($attributes, $fieldset, $exclude=array())
    {
        $this->_addElementTypes($fieldset);
        foreach ($attributes as $attribute) {
            if (!$attribute || ($attribute->hasIsVisible() && !$attribute->getIsVisible())) {
                continue;
            }
            if ( ($inputType = $attribute->getFrontend()->getInputType())
                 && !in_array($attribute->getAttributeCode(), $exclude)
                 && ('media_image' != $inputType)
                 ) {

                $fieldType      = $inputType;
                $rendererClass  = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType  = $inputType . '_' . $attribute->getAttributeCode();
                    $fieldset->addType($fieldType, $rendererClass);
                }

                $element = $fieldset->addField($attribute->getAttributeCode(), $fieldType,
                    array(
                        'name'      => $attribute->getAttributeCode(),
                        'label'     => Mage::helper('magemenu')->__($attribute->getFrontend()->getLabel()),
                        'class'     => $attribute->getFrontend()->getClass(),
                        'required'  => $attribute->getIsRequired(),
                        'note'      => Mage::helper('magemenu')->__($attribute->getNote()),
                    )
                )
                ->setEntityAttribute($attribute);

                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                if ($inputType == 'select' || $inputType == 'multiselect') {
                    $element->setValues($attribute->getSource()->getAllOptions(true, true));
                } elseif ($inputType == 'date') {
                    $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
                    $element->setFormat(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                }
            }
        }
    }
    
}
