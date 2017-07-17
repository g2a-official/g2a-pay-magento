<?php
/**
 * G2A Pay order item interface.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface G2A_Pay_Model_Order_Item_Interface
{
    /**
     * Gets current item Sku.
     *
     * @return string
     */
    public function getSku();

    /**
     * Gets current item amount.
     *
     * @return float
     */
    public function getAmount();

    /**
     * Gets current item name.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets current item type.
     *
     * @return string
     */
    public function getType();

    /**
     * Gets current item ID.
     *
     * @return string
     */
    public function getId();

    /**
     * Gets current item price.
     *
     * @return string
     */
    public function getPrice();

    /**
     * Gets current item url.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Gets current item extra description.
     *
     * @return string
     */
    public function getExtra();

    /**
     * Gets current item quantity.
     *
     * @return int
     */
    public function getQuantity();
}
