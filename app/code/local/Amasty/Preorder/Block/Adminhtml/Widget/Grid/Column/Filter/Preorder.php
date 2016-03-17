<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

class Amasty_Preorder_Block_Adminhtml_Widget_Grid_Column_Filter_Preorder
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    protected function _getOptions()
    {
        return array(
            array(
                'value' => null,
                'label' => null,
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('ampreorder')->__('Pre Orders Only'),
            ),
            array(
                'value' => 0,
                'label' => Mage::helper('ampreorder')->__('Exclude Pre Orders'),
            ),
        );
    }

    public function getCondition()
    {
        $condition = parent::getCondition();

        if ($condition && isset($condition['eq']) && $condition['eq'] == 0) {
            //Include row without pre-order info
            $condition = array(
                $condition,
                array(
                    'null' => true,
                ),
            );
        }

        return $condition;
    }
}
