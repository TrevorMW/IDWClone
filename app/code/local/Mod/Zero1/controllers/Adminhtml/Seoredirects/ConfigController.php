<?php

require_once Mage::getModuleDir('controllers', 'Zero1_Seoredirects').DS.'Adminhtml'.DS.'Seoredirects'.DS. 'ConfigController.php';

class Mod_Zero1_Adminhtml_ConfigController extends Zero1_Seoredirects_Adminhtml_Seoredirects_ConfigController
{
	/**
	 * Check the ACL to run this controller
	 *
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('system/config/seoredirects');
	}
}
