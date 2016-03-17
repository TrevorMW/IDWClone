<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */
class Amasty_Preorder_Block_Warning extends Mage_Core_Block_Template
{
    const TEMPLATE_ORDER_VIEW = 'amasty/ampreorder/warning_order_view.phtml';
    const TEMPLATE_ORDER_EMAIL = 'amasty/ampreorder/warning_order_email.phtml';

    protected $_warningText;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(self::TEMPLATE_ORDER_VIEW);
    }

    protected function getWarningText()
    {
        return $this->_warningText;
    }

    public function setWarningText($warningText)
    {
        $this->_warningText = $warningText;
    }
}