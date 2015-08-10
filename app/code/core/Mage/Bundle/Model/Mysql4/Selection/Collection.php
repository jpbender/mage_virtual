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
 * Bundle Selections Resource Collection
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Model_Mysql4_Selection_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    protected $_selectionTable;

    /**
     * Initialize collection
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setRowIdFieldName('selection_id');
        $this->_selectionTable = $this->getTable('bundle/selection');
    }

    /**
     * Initialize collection select
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->join(array('selection' => $this->_selectionTable),
            '`selection`.`product_id`=`e`.`entity_id`',
            array('*')
        );
    }

    /**
     * Join website scope prices to collection, override default prices
     *
     * @return Mage_Bundle_Model_Mysql4_Selection_Collection
     */
    public function joinPrices($websiteId)
    {
        $this->getSelect()->joinLeft(array('price' => $this->getTable('bundle/selection_price')),
            'selection.selection_id = price.selection_id AND price.website_id = ' . $websiteId,
             array(
                'selection_price_type' => 'IFNULL(price.selection_price_type, selection.selection_price_type)',
                'selection_price_value' => 'IFNULL(price.selection_price_value, selection.selection_price_value)',
                'price_scope' => 'price.website_id'
            )
        );
        return $this;
    }

    /**
     * Apply option ids filter to collection
     *
     * @return Mage_Bundle_Model_Mysql4_Selection_Collection
     */
    public function setOptionIdsFilter($optionIds)
    {
        if (!empty($optionIds)) {
            $this->getSelect()->where('`selection`.`option_id` in (' . join(',', (array)$optionIds) . ')');
        }
        return $this;
    }

    /**
     * Apply selection ids filter to collection
     *
     * @return Mage_Bundle_Model_Mysql4_Selection_Collection
     */
    public function setSelectionIdsFilter($selectionIds)
    {
        if (!empty($selectionIds)) {
            $this->getSelect()->where('`selection`.`selection_id` in (' . join(',', (array)$selectionIds) . ')');
        }
        return $this;
    }

    /**
     * Set position order
     *
     * @return Mage_Bundle_Model_Mysql4_Selection_Collection
     */
    public function setPositionOrder()
    {
        $this->getSelect()->order('selection.position asc')
            ->order('selection.selection_id asc');
        return $this;
    }
}
