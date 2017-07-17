<?php
/**
 * G2A Pay other data wrapper.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Order_Item_Other implements G2A_Pay_Model_Order_Item_Interface
{
    const OTHER_SKU  = 'other';
    const OTHER_NAME = 'Other';
    const OTHER_TYPE = 'other';
    const OTHER_ID   = 'other';

    /** @var float */
    private $_amount;

    /**
     * @param float $amount
     */
    public function __construct($amount)
    {
        $this->_amount = $amount;
    }

    /**
     * Get discount sku.
     *
     * @return string
     */
    public function getSku()
    {
        return self::OTHER_SKU;
    }

    /**
     * Get discount amount.
     *
     * @return mixed
     */
    public function getAmount()
    {
        /** @var G2A_Pay_Helper_Order $helper */
        $helper = Mage::helper('g2apay/order');

        return $helper->roundPrice($this->_amount);
    }

    /**
     * Get discount name.
     *
     * @return string
     */
    public function getName()
    {
        return self::OTHER_NAME;
    }

    /**
     * Get discount type.
     *
     * @return string
     */
    public function getType()
    {
        return self::OTHER_TYPE;
    }

    /**
     * Get discount id.
     *
     * @return string
     */
    public function getId()
    {
        return self::OTHER_ID;
    }

    /**
     * Get discount price.
     *
     * @return string
     */
    public function getPrice()
    {
        /** @var G2A_Pay_Helper_Order $helper */
        $helper = Mage::helper('g2apay/order');

        return $helper->roundPrice($this->_amount);
    }

    /**
     * Get discount url.
     *
     * @return string
     */
    public function getUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
    }

    /**
     * Get discount extra info.
     *
     * @return string
     */
    public function getExtra()
    {
        return '';
    }

    /**
     * Get discount quantity.
     *
     * @return int
     */
    public function getQuantity()
    {
        return 1;
    }
}
