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
 * @package     Mage_Tax
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Tax report collection
 *
 * @category   Mage
 * @package    Mage_Tax
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tax_Model_Mysql4_Report_Collection extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    protected $_periodFormat;
    protected $_selectedColumns = array();

    /**
     * Initialize custom resource model
     *
     * @param array $parameters
     */
    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init('tax/tax_order_aggregated_created');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    protected function _getSelectedColumns()
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = 'DATE_FORMAT(period, \'%Y-%m\')';
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = 'EXTRACT(YEAR FROM period)';
        } else {
            $this->_periodFormat = 'period';
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = array(
                'period'                => $this->_periodFormat,
                'code'                  => 'code',
                'percent'               => 'percent',
                'orders_count'          => 'sum(orders_count)',
                'tax_base_amount_sum'   => 'sum(tax_base_amount_sum)'
            );
        }

        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }

        if ($this->isSubTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns() + array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }

    /**
     * Add selected data
     *
     * @return Mage_Tax_Model_Mysql4_Report_Collection
     */
    protected  function _initSelect()
    {
        $this->getSelect()->from($this->getResource()->getMainTable() , $this->_getSelectedColumns());
        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat,
                'code'
            ));
        }

        if ($this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat
            ));
        }

        return $this;
    }

}
