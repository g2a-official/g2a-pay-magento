<?php
/**
 * G2A Pay shipment data wrapper.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Order_Item_Shipment implements G2A_Pay_Model_Order_Item_Interface
{
    const DEFAULT_SHIPMENT_SKU  = 'shipping';
    const DEFAULT_SHIPMENT_NAME = 'Shipping';
    const DEFAULT_SHIPMENT_TYPE = 'shipping';
    const DEFAULT_SHIPMENT_ID   = 'shipping';

    /** @var Mage_Sales_Model_Order */
    private $_order;

    /**
     * @param Mage_Sales_Model_Order $order
     */
    public function __construct(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
    }

    /**
     * Get discount sku.
     *
     * @return string
     */
    public function getSku()
    {
        $carrier = $this->_order->getShippingCarrier();

        $sku = null;

        if ($carrier instanceof Mage_Shipping_Model_Carrier_Abstract) {
            $sku = $carrier->getCarrierCode();
        }

        if (empty($sku)) {
            $sku = self::DEFAULT_SHIPMENT_SKU;
        }

        return $sku;
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

        return $helper->roundPrice($this->_order->getShippingInclTax());
    }

    /**
     * Get discount name.
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->_order->getShippingDescription();

        if (empty($name)) {
            $name = self::DEFAULT_SHIPMENT_NAME;
        }

        return $name;
    }

    /**
     * Get discount type.
     *
     * @return string
     */
    public function getType()
    {
        return self::DEFAULT_SHIPMENT_TYPE;
    }

    /**
     * Get discount Id.
     *
     * @return string
     */
    public function getId()
    {
        return self::DEFAULT_SHIPMENT_ID;
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

        return $helper->roundPrice($this->_order->getShippingInclTax());
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
