<?php

require_once Mage::getModuleDir('controllers', 'Zero1_Seoredirects').DS.'Adminhtml'.DS.'Seoredirects'.DS. 'ImportController.php';

class Mod_Zero1_Adminhtml_Seoredirects_ImportController extends Zero1_Seoredirects_Adminhtml_Seoredirects_ImportController
{
    /**
     * Check the ACL to run this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/seoredirects/import');
    }
}

