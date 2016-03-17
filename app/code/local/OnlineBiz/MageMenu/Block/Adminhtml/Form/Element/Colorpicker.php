<?php
/**
 * OnlineBiz_MageMenu_Block_Adminhtml_Form_Element_Colorpicker
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Block_Adminhtml_Form_Element_Colorpicker extends Varien_Data_Form_Element_Text
{
    /**
     * @var Hex color
     */
    protected $_value;
    
	public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('text');
        $this->setExtType('textfield');
        if (isset($attributes['value'])) {
            $this->setValue($attributes['value']);
        }
    }
    
    public function getElementHtml()
    {
        $this->addClass('input-text');

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s style="width:110px;" />'
            . ' &nbsp; <button alt="" id="%s_swatch" title="%s" style="background:none; border:0px; width:30px;  height:30px; font-size:8px; float:right; margin-right:140px" %s>%s</button>',
            $this->getName(), $this->getHtmlId(), $this->_escape($this->getValue()), $this->serialize($this->getHtmlAttributes()),
            $this->getHtmlId(), __('Select Color'), ($this->getDisabled() ? 'disabled="true"' : ''), '&nbsp;'
        );

        $html .= sprintf('
            <script type="text/javascript">
            //<![CDATA[
                new Control.ColorPicker("%s", { IMAGE_BASE: "%s", "swatch" : "%s" });
            //]]>
            </script>',
            $this->getHtmlId(), 
            Mage::getBaseUrl('js') . '/colorpicker/img/',
            $this->getHtmlId() . '_swatch'
        );

        $html .= $this->getAfterElementHtml();

        return $html;
    	
    }
}
