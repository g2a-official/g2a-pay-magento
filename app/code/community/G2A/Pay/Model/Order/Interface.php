<?php
/**
 * G2A Pay oder interface.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface G2A_Pay_Model_Order_Interface
{
    /**
     * Gets identifier of the order.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Gets currency (ISO 4217) of the order
     * Gets current order.
     *
     * @return string
     */
    public function getCurrency();

    /**
     * Gets amount of the order.
     *
     * @return float
     */
    public function getAmount();

    /**
     * Gets email of the order's owner.
     *
     * @return string
     */
    public function getOwnerEmail();

    /**
     * Gets id of the order's owner.
     *
     * @return string
     */
    public function getOwnerId();

    /**
     * Gets items array of the order.
     *
     * @return array
     */
    public function getItems();

    /**
     * Gets items array of the order.
     *
     * @return array
     */
    public function getPaymentMethodCode();

    /**
     * Is order new.
     *
     * @return bool
     */
    public function isNew();

    /**
     * Updates transaction id.
     *
     * @param $id
     */
    public function setTransactionId($id);

    /**
     * Gets transaction id.
     *
     * @return mixed
     */
    public function getTransactionId();

    /**
     * Marks order as complete.
     *
     * @param string $message
     */
    public function complete($message = '');

    /**
     * Applies order refund.
     *
     * @param $refund
     * @param string $message
     */
    public function refund($refund, $message = '');

    /**
     * Cancels order.
     *
     * @param string $message
     */
    public function cancel($message = '');

    /**
     * Rejects order.
     *
     * @param string $message
     */
    public function reject($message = '');

    /**
     * Gets order log data.
     *
     * @return mixed
     */
    public function getLog();
}
