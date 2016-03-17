<?php

/**
 * @author Luis E. Tineo <luis@codegrease.com>
 * @package IndustryWest
 * @subpackage IndustryWest_CustomTabs
 * @copyright  Copyright (c) 2015   CodeGrease, LLC (http://www.codegrease.com)
 */
class IndustryWest_CustomTabs_Block_Tabs_Warranty extends Mage_Catalog_Block_Product_View
{

    protected function getFilter()
    {
      return Mage::getModel('cms/template_filter');
    }

    protected function getWarrantyCare()
    {
      return $this->getFilter()->filter( $this->getProduct()->getData("warranty_information") );
    }

}
