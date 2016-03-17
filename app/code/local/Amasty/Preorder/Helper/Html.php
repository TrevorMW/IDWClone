<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */
class Amasty_Preorder_Helper_Html extends Mage_Core_Helper_Abstract
{
    /**
     * @param $html
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function injectAdminProductPreorderNoteField($html, Mage_Catalog_Model_Product $product = null)
    {
        $productNoteBlock = $this->getAdminFieldsBlock();
        $productNoteBlock->setProduct($product);

        if (preg_match('@<tr.*?id="inventory_backorders".*?</tr>@s', $html, $match)) {
            $blockHTML = $productNoteBlock->toHtml();
            $replacement = $match[0] . PHP_EOL . $blockHTML;
            $html = str_replace($match[0], $replacement, $html);
        }

        return $html;
    }

    public function injectAdminProductInventoryJS($html)
    {
        $search = 'var catalogInventoryNotManageStockFields = {';
        $replacement = $search . PHP_EOL . 'inventory_preorder_note: true,';
        $html = str_replace($search, $replacement, $html);
        return $html;
    }

    public function injectAdminOrderItemPreorderStatus($html, Mage_Sales_Model_Order_Item $orderItem)
    {
        $isPreorder = $this->getDataHelper()->getOrderItemIsPreorderFlag($orderItem->getId());

        if ($isPreorder) {
            $search = '</h5>';
            $replace = ' ' . $this->getPreorderTag() . $search;
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }

    public function injectProductViewPreorderNote($html, Mage_Catalog_Model_Product $product)
    {
        if ($product->getTypeInstance() instanceof Mage_Catalog_Model_Product_Type_Grouped) {
            $html = $this->injectProductGroupViewPreorderNote($html);
        } else {
            $pattern = '@<p[^>]*class="availability.*?>(.*?)</p>@s';
            if (preg_match($pattern, $html, $match)) {
                $noteBlock = $this->getPreorderNoteBlock();
                $noteBlock->setProduct($product);
                $blockHTML = $noteBlock->toHtml();

                if ($blockHTML) {
                    $html = str_replace($match[1], $blockHTML, $html);
                }
            }
        }

        return $html;
    }

    protected function injectProductGroupViewPreorderNote($html)
    {
        $noteBlock = $this->getPreorderNoteBlock();

        preg_match_all('@<div[^>]+price-box.*?product-price-(\d+).*?</div>@s', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product');
            $product->load($match[1]);

            $noteBlock->setProduct($product);
            $blockHTML = $noteBlock->toHtml();

            $result = $blockHTML . PHP_EOL . $match[0];
            $html = str_replace($match[0], $result, $html);
        }

        return $html;
    }

    public function injectCompositeProductViewJS($html, Mage_Catalog_Model_Product $product)
    {
        if (!$product->isComposite()) {
            throw new Exception('Invalid call: Using composite method for elementary product');
        }

        /** @var Amasty_Preorder_Block_Product_View_Composite $block */
        $block = Mage::app()->getLayout()->createBlock('ampreorder/product_view_composite_' . $product->getTypeId());
        $block->setProduct($product);
        $blockHTML = $block->toHtml();

        $html = $html . PHP_EOL . $blockHTML;
        return $html;
    }

    public function injectProductListPreorderNote($html)
    {
        $pattern = '@(?:id="product-(?:minimal-)?price-(\d+).*?</div>|<[^>]*price-to[^>]*amcart-(\d+).*?</div>)@s'; //<[^>]*amcart-(\d+).*?</p>
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        $ids = array();
        foreach ($matches as $match) {
            $id = $match[1] ? $match[1] : $match[2];
            $ids[] = $id;
        }

        /** @var Mage_Catalog_Model_Product $model */
        $model = Mage::getModel('catalog/product');
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = $model->getCollection();
        $collection->addFieldToFilter('entity_id', array('in', $ids));

        $noteBlock = $this->getPreorderNoteBlock();
        foreach ($matches as $match) {
            $id = $match[1] ? $match[1] : $match[2];
            /** @var Mage_Catalog_Model_Product $product */
            $product = $collection->getItemById($id);
            if (is_null($product)) {
                Mage::log('Preorder: Cannot load product #'.$id, Zend_Log::WARN);
                continue;
            }

            $noteBlock->setProduct($product);
            $blockHTML = $noteBlock->toHtml();

            $replacement = $match[0] . PHP_EOL . $blockHTML;
            if (strpos($html, $replacement) === false) {
                $html = str_replace($match[0], $replacement, $html);
            }
        }

        return $html;
    }

    public function injectProductListCartButtonLabel($html)
    {
        $html = $this->injectProductListCartButtonLabelSimple($html);
        $html = $this->injectProductListCartButtonLabelComposite($html);

        return $html;
    }

    protected function injectProductListCartButtonLabelSimple($html)
    {
        $pattern = '@<button[^>]*btn-cart[^>]*/product/(\d+).*?</button>@s';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        $ids = array();
        foreach ($matches as $match) {
            $ids[] = $match[1];
        }
        /** @var Mage_Catalog_Model_Product $model */
        $model = Mage::getModel('catalog/product');
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = $model->getCollection();
        $collection->addFieldToFilter('entity_id', array('in', $ids));

        foreach ($matches as $match) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = $collection->getItemById($match[1]);
            if (is_null($product)) {
                Mage::log('Preorder: Cannot load product #'.$match[1], Zend_Log::WARN);
                continue;
            }

            $replacement = $this->injectCartButtonLabel($match[0], $product);
            $html = str_replace($match[0], $replacement, $html);
        }

        return $html;
    }

    protected function injectProductListCartButtonLabelComposite($html)
    {
        $buttons = $this->matchProductListCartButtonUrlKeys($html);

        /** @var Mage_Catalog_Model_Product $model */
        $model = Mage::getModel('catalog/product');
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = $model->getCollection();
        $collection->addAttributeToFilter('url_key', array('in', array_keys($buttons)));

        foreach ($buttons as $urlKey => $button) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = $collection->getItemByColumnValue('url_key', $urlKey);
            if (is_null($product)) {
                Mage::log('Preorder: Cannot load product by url_key: '.$urlKey, Zend_Log::WARN);
                continue;
            }

            $replacement = $this->injectCartButtonLabel($button, $product);
            $html = str_replace($button, $replacement, $html);
        }

        return $html;
    }

    /**
     * @param $html
     * @return array [url_key] => $match[0]
     */
    protected function matchProductListCartButtonUrlKeys($html)
    {
        $pattern = '@<button[^>]*btn-cart[^>]*https?://[^>]*/([^/?#\']+).*?</button>@s';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
        if ($suffix && '/' != $suffix && '.' != $suffix[0]){
            $suffix = '.' . $suffix;
        }

        $result = array();
        foreach ($matches as &$match) {
            $key = $match[1];

            if (preg_match('@\d+@', $key)) {
                continue;
            }

            if ($suffix != '') {
                $l = strlen($suffix);
                if (substr_compare($key, $suffix, -$l) == 0) {
                    $key = substr($key, 0, -$l);
                }
            }

            $result[$key] = $match[0];
        }

        return $result;
    }

    public function injectCartButtonLabel($html, Mage_Catalog_Model_Product $product)
    {
        if ($this->getDataHelper()->getIsProductPreorder($product)) {
            $pattern = '@(<button[^>]*btn-cart.*?<span[^>]*>)[^<>]+(</span>.*?</button>)@s';
            if (preg_match($pattern, $html, $match)) {
                $label = $this->getDataHelper()->getProductPreorderCartLabel($product);
                $replacement = $match[1] . $label . $match[2];
                $html = str_replace($match[0], $replacement, $html);
            }
        }

        return $html;
    }

    public function injectCartPreorderNote($html)
    {
        /** @var Mage_Checkout_Model_Cart $cart */
        $cart = Mage::getSingleton('checkout/cart');
        $quote = $cart->getQuote();

        $pattern = '@<tr.*?cart\[([\d]+)\]\[qty\].*?</tr>@s';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $originHTML = $match[0];

            $quoteItem = $quote->getItemById($match[1]);
            if (is_null($quoteItem)) {
                // Workaround for Amasty Auto Add Promo Items module
                continue;
            }

            if ($this->getDataHelper()->getQuoteItemIsPreorder($quoteItem)) {
                if ($quoteItem->getProductType() == 'bundle') {
                    $replacement = $this->injectCartBundlePreorderNote($originHTML, $quoteItem);
                } else {
                    $replacement = $originHTML;
                    if (preg_match('@<h2[^>]class="product-name".*?</h2>@s', $originHTML, $matchBundle)) {
                        $noteBlock = $this->getPreorderNoteBlock(Amasty_Preorder_Block_Note::TEMPLATE_CART);
                        $noteBlock->setQuoteItem($quoteItem);
                        $blockHTML = $noteBlock->toHtml();

                        $replacementBundle = $matchBundle[0] . PHP_EOL . $blockHTML;
                        $replacement = str_replace($matchBundle[0], $replacementBundle, $originHTML);
                    }
                }

                $html = str_replace($originHTML, $replacement, $html);
            }
        }

        return $html;
    }

    protected function injectCartBundlePreorderNote($html, Mage_Sales_Model_Quote_Item $item)
    {
        foreach ($item->getChildren() as $childItem) {
            /** @var Mage_Sales_Model_Quote_Item $childItem */
            $product = $childItem->getProduct();
            if ($this->getDataHelper()->getIsProductPreorder($product)) {

                $pattern = '@(<dd>[^<]*' . preg_quote($product->getName(), '@') . '.*?)(</dd>)@s';
                if (preg_match($pattern, $html, $match)) {
                    $block = $this->getPreorderNoteBlock(Amasty_Preorder_Block_Note::TEMPLATE_GENERIC);
                    $block->setQuoteItem($childItem);

                    $replacement = $match[1] . PHP_EOL . '<br />' . $block->toHtml() . PHP_EOL . $match[2];
                    $html = str_replace($match[0], $replacement, $html);
                }
            }
        }

        return $html;
    }

    public function injectOrderListPreorderTag($html)
    {
        $dataHelper = $this->getDataHelper();
        $pattern = '@<tr.*?<td.*?(\d+).*?</tr>@s';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $orderHTML = $match[0];

            $isPreorder = $dataHelper->getOrderIsPreorderFlagByIncrementId($match[1]);

            if ($isPreorder) {
                if (preg_match('@<em>[^<]+@s', $orderHTML, $matchOrder)) {
                    $replacementOrder = $matchOrder[0] . PHP_EOL . $this->getPreorderTag();
                    $orderHTML = str_replace($matchOrder[0], $replacementOrder, $orderHTML);
                }

                $html = str_replace($match[0], $orderHTML, $html);
            }
        }

        return $html;
    }

    public function injectOrderViewPreorderWarning($html, Mage_Sales_Model_Order $order)
    {
        $state = $order->getState();
        $isStateAllowed = in_array($state, array(
            Mage_Sales_Model_Order::STATE_HOLDED,
            Mage_Sales_Model_Order::STATE_NEW,
            Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            Mage_Sales_Model_Order::STATE_PROCESSING,
        ));

        $isPreorder = $this->getDataHelper()->getOrderIsPreorderFlag($order);

        if ($isStateAllowed && $isPreorder) {
            if (preg_match('@<div[^>]*?page-title.*?</div>@s', $html, $match)) {
                $warningBlock = $this->getPreorderWarningBlock();

                $warningText = $this->getDataHelper()->getOrderPreorderWarning($order->getId());
                $warningBlock->setWarningText($warningText);

                $warningHTML = $warningBlock->toHtml();
                $replacement = $match[0] . PHP_EOL . $warningHTML;
                $html = str_replace($match[0], $replacement, $html);
            }
        }

        return $html;
    }

    public function injectEmailOrderConfirmationPreorderWarning($html, Mage_Sales_Model_Order $order)
    {
        $isPreorder = $this->getDataHelper()->getOrderIsPreorderFlag($order);
        if ($isPreorder) {
            $warningBlock = $this->getPreorderWarningBlock(Amasty_Preorder_Block_Warning::TEMPLATE_ORDER_EMAIL);
            $warningBlock->setWarningText($this->getDataHelper()->getOrderPreorderWarning($order->getId()));
            $warningHTML = $warningBlock->toHtml();
            $html = $html . PHP_EOL . $warningHTML;
        }

        return $html;
    }

    /**
     * @return Amasty_Preorder_Block_Adminhtml_Product_Preorder
     */
    protected function getAdminFieldsBlock()
    {
        return Mage::app()->getLayout()->createBlock('ampreorder/adminhtml_product_preorder');
    }

    /**
     * @param string $template
     * @return Amasty_Preorder_Block_Note
     */
    protected function getPreorderNoteBlock($template = null)
    {
        /** @var Amasty_Preorder_Block_Note $block */
        $block = Mage::app()->getLayout()->createBlock('ampreorder/note');
        if (isset($template)) {
            $block->setTemplate($template);
        }
        return $block;
    }

    /**
     * @param string $template
     * @return Amasty_Preorder_Block_Warning
     */
    protected function getPreorderWarningBlock($template = null)
    {
        /** @var Amasty_Preorder_Block_Warning $block */
        $block = Mage::app()->getLayout()->createBlock('ampreorder/warning');
        if (isset($template)) {
            $block->setTemplate($template);
        }
        return $block;
    }

    /**
     * @return Amasty_Preorder_Helper_Data
     */
    public function getDataHelper()
    {
        return Mage::helper('ampreorder');
    }

    public function getPreorderTag()
    {
        return $this->__('(Preorder)');
    }
}
