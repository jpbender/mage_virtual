<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Shopping cart item render block
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
    /**
     * Get bundled selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @return array
     */
    protected function _getBundleOptions($useCache = true)
    {
        $options = array();

        /**
         * @var Mage_Bundle_Model_Product_Type
         */
        $typeInstance = $this->getProduct()->getTypeInstance(true);

        // get bundle options
        $optionsQuoteItemOption =  $this->getItem()->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = unserialize($optionsQuoteItemOption->getValue());
        if ($bundleOptionsIds) {
            /**
            * @var Mage_Bundle_Model_Mysql4_Option_Collection
            */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $this->getProduct());

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $this->getItem()->getOptionByCode('bundle_selection_ids');

            $selectionsCollection = $typeInstance->getSelectionsByIds(
                unserialize($selectionsQuoteItemOption->getValue()),
                $this->getProduct()
            );

            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections()) {
                    $option = array(
                        'label' => $bundleOption->getTitle(),
                        'value' => array()
                    );

                    $bundleSelections = $bundleOption->getSelections();

                    foreach ($bundleSelections as $bundleSelection) {
                        $option['value'][] = $this->_getSelectionQty($bundleSelection->getSelectionId()).' x '. $this->htmlEscape($bundleSelection->getName()). ' ' .Mage::helper('core')->currency($this->_getSelectionFinalPrice($bundleSelection));
                    }

                    $options[] = $option;
                }
            }
        }

        return $options;
    }

    /**
     * Obtain final price of selection in a bundle product
     *
     * @param Mage_Catalog_Model_Product $selectionProduct
     * @return decimal
     */
    protected function _getSelectionFinalPrice($selectionProduct)
    {
        $bundleProduct = $this->getProduct();
        return $bundleProduct->getPriceModel()->getSelectionFinalPrice(
            $bundleProduct, $selectionProduct,
            $this->getQty(),
            $this->_getSelectionQty($selectionProduct->getSelectionId())
        );
    }

    /**
     * Get selection quantity
     *
     * @param int $selectionId
     * @return decimal
     */
    protected function _getSelectionQty($selectionId)
    {
        if ($selectionQty = $this->getProduct()->getCustomOption('selection_qty_' . $selectionId)) {
            return $selectionQty->getValue();
        }
        return 0;
    }

    /**
     * Overloaded method for getting list of bundle options
     * Caches result in quote item, because it can be used in cart 'recent view' and on same page in cart checkout
     *
     * @return array
     */
    public function getOptionList()
    {
        $item = $this->getItem();
        $optionList = $item->getBlockOptionList();
        if ($optionList === null) {
            $optionList = array_merge($this->_getBundleOptions(), parent::getOptionList());
            $item->setBlockOptionList($optionList);
        }

        return $optionList;
    }

    /**
     * Return cart backorder messages
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = $this->getData('messages');
        if (is_null($messages)) {
            $messages = array();
        }
        $options = $this->getItem()->getQtyOptions();

        foreach ($options as $option) {
            if ($option->getMessage()) {
                $messages[] = array(
                    'text' => $option->getMessage(),
                    'type' => ($this->getItem()->getHasError()) ? 'error' : 'notice'
                );
            }
        }

        return $messages;
    }
}