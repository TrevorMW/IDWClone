<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

class Amasty_Preorder_Block_Adminhtml_Widget_Grid_Column_Renderer_Preorder
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_defaultWidth = 50;

    public function render(Varien_Object $row)
    {
        $isPreorder = $this->_getValue($row);

        $indicator = $isPreorder ? 'Yes' : 'No';

        return $this->__($indicator);
    }
}
