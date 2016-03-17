<?php

class Mango_Attributeswatches_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getAttributesWithSwatches() {
        $_att = array();
        $_image_swatches = Mage::getStoreConfig('attributeswatches/settings/attributes', Mage::app()->getStore());
        $_image_swatches = explode(",", $_image_swatches);
        foreach ($_image_swatches as $x) {
            $_att[$x] = "image";
        }
        $_label_swatches = Mage::getStoreConfig('attributeswatches/settings/labels', Mage::app()->getStore());
        $_label_swatches = explode(",", $_label_swatches);
        foreach ($_label_swatches as $x) {
            $_att[$x] = "label";
        }
        return $_att;
    }

    public function getAttributesWithSwatchesProductView() {
        $_att = array();
        $_image_swatches = Mage::getStoreConfig('attributeswatches/settings/attributes', Mage::app()->getStore());
        $_image_swatches = explode(",", $_image_swatches);
        foreach ($_image_swatches as $x) {
            $_att[$x] = "image";
        }
        $_label_swatches = Mage::getStoreConfig('attributeswatches/settings/labels', Mage::app()->getStore());
        $_label_swatches = explode(",", $_label_swatches);
        foreach ($_label_swatches as $x) {
            $_att[$x] = "label";
        }
        $_child_swatches = Mage::getStoreConfig('attributeswatches/settings/childproducts', Mage::app()->getStore());
        $_child_swatches = explode(",", $_child_swatches);
        foreach ($_child_swatches as $x) {
            $_att[$x] = "child";
        }
        return $_att;
    }

    public function getAttributesProductViewHideSelect() {
        $_att = array();
        $_hideselect = Mage::getStoreConfig('attributeswatches/settings/hideselect', Mage::app()->getStore());
        $_hideselect = explode(",", $_hideselect);
        foreach ($_hideselect as $x) {
            $_att[$x] = "hideselect";
        }

        return $_att;
    }

    public function getAttributesSwitchGalleryProductView() {
        $_att = array();
        $_atts = Mage::getStoreConfig('attributeswatches/settings/switchimage', Mage::app()->getStore());
        $_atts = explode(",", $_atts);
        foreach ($_atts as $x) {
            $_att[$x] = "switchgallery";
        }
        return $_att;
    }

    public function getAttributesWithSwatchesList() {
        return Mage::getStoreConfig('attributeswatches/settings/list', Mage::app()->getStore());
    }

    /* public function getAttributesWithImagesSwatches(){
      $_image_swatches = Mage::getStoreConfig('attributeswatches/settings/images_swatches'  );
      $_image_swatches = explode(",", $_image_swatches);
      if(count($_image_swatches))return $_image_swatches;
      return array();
      } */

    public function getAttributesWithSwatchesForSQL() {
        $_swatches = Mage::getStoreConfig("attributeswatches/settings/attributes")
                . "," . Mage::getStoreConfig("attributeswatches/productlist/attributes")
                . "," . Mage::getStoreConfig("attributeswatches/layerednavigation/attributes")
                . ", " . Mage::getStoreConfig("attributeswatches/layerednavigation/hidelabel");
        //$_swatches = array_unique(explode(",", $_swatches));
        //print_r($_swatches);
        //exit;
        $_swatches  = array_unique(array_map('trim', explode(',', $_swatches)));  
        
        
        if (count($_swatches))
            return "'" . join("','", $_swatches) . "'";
        return false;
    }

    //public function getAttributesWithSwatches
}