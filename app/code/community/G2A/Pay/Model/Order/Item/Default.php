<?php
/**
 * G2A Pay Mage_Sales_Model_Order_Item order item wrapper.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Order_Item_Default implements G2A_Pay_Model_Order_Item_Interface
{
    /** @var Mage_Sales_Model_Order_Item */
    private $_item;

    /**
     * @param Mage_Sales_Model_Order_Item $item
     */
    public function __construct(Mage_Sales_Model_Order_Item $item)
    {
        $this->_item = $item;
    }

    /**
     * Get item sku.
     *
     * @return string
     */
    public function getSku()
    {
        return $this->_item->getSku();
    }

    /**
     * Get item amount.
     *
     * @return mixed
     */
    public function getAmount()
    {
        /** @var G2A_Pay_Helper_Order $helper */
        $helper            = Mage::helper('g2apay/order');
        $totalOrderedPrice = $this->_item->getPrice() * $this->_item->getQtyOrdered();

        return $helper->roundPrice($totalOrderedPrice);
    }

    /**
     * Get item name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_item->getName();
    }

    /**
     * Get item type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_item->getProductType();
    }

    /**
     * Get product ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_item->getProductId();
    }

    /**
     * Get item Price.
     *
     * @return string
     */
    public function getPrice()
    {
        /** @var G2A_Pay_Helper_Order $helper */
        $helper            = Mage::helper('g2apay/order');
        $totalOrderedPrice = $this->_item->getPrice(); //TODO: Tu Damian mówi, żę trzeba obliczyć bo się na zaokrągleniach wyjebie :)

        return $helper->roundPrice($totalOrderedPrice);
    }

    /**
     * Get product Url.
     *
     * @return string
     */
    public function getUrl()
    {
        return Mage::getUrl('catalog/product/view', array('id' => $this->_item->getProductId()));
    }

    /**
     * Get item extra info.
     *
     * @return string
     */
    public function getExtra()
    {
        return $this->_item->getDescription();
    }

    /**
     * Get item quantity.
     *
     * @return mixed
     */
    public function getQuantity()
    {
        return (int) $this->_item->getQtyOrdered();
    }
}
