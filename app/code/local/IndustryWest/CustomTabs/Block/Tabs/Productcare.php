<?php

/**
 * @author Trevor M. Wagner <trevor@cobblehilldigital.com>
 * @package IndustryWest
 * @subpackage IndustryWest_CustomTabs
 * @copyright  Copyright (c) 2015   Cobble Hill, LLC (http://www.cobblehilldigital.com)
 */


class IndustryWest_CustomTabs_Block_Tabs_Productcare extends Mage_Catalog_Block_Product_View
{

    protected function getFilter()
    {
        return Mage::getModel('cms/template_filter');
    }

    protected function getProductCare()
    {
        return $this->getFilter()->filter( $this->getProduct()->getData("product_care") );
    }

}
