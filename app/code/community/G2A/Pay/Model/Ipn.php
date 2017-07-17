<?php
/**
 * G2A Pay IPN processing model.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Ipn extends G2A_Pay_Model_Base
{
    /**
     * G2A Pay IPN data template.
     *
     * @var array
     */
    protected static $IPN_DATA_TEMPLATE = array(
        'transactionId'   => null,
        'userOrderId'     => null,
        'amount'          => 0,
        'currency'        => null,
        'status'          => null,
        'orderCreatedAt'  => null,
        'orderCompleteAt' => null,
        'refundedAmount'  => null,
        'hash'            => null,
    );

    /**
     * G2A Pay IPN allowed statuses.
     *
     * @var array
     */
    protected static $IPN_STATUSES = array('complete', 'partial_refunded', 'refunded', 'rejected', 'canceled');

    /**
     * Process G2A Pay IPN data.
     *
     * @param $data
     * @throws Exception
     */
    public function processIpnData($data)
    {
        $this->validateIpnDataFormat($data);
        $data = $this->filterData($data);

        /** @var G2A_Pay_Model_Factory $factory */
        $factory = Mage::getSingleton('g2apay/factory');
        $order   = $factory->getOrderById($data['userOrderId']);

        $this->validateOrder($order);
        $this->validateOrderHash($order, $data['transactionId'], $data['hash']);
        $this->validateOrderData($order, $data);

        $this->updateOrderData($order, $data);
    }

    /**
     * Gets IPN url optional secret.
     *
     * @return null|string
     */
    public function getIpnSecret()
    {
        return $this->_config->getIpnSecret();
    }

    /**
     * Validates IPN data format.
     *
     * @param $data
     * @throws Exception
     */
    protected function validateIpnDataFormat($data)
    {
        if (!is_array($data)) {
            throw new G2A_Pay_Exception_InvalidInput('Invalid ipn data format');
        }
    }

    /**
     * Filters IPN data.
     *
     * @param $data
     * @return array
     */
    protected function filterData($data)
    {
        $data = array_merge(static::$IPN_DATA_TEMPLATE, $data);
        $data = array_intersect_key($data, static::$IPN_DATA_TEMPLATE);

        return $data;
    }

    /**
     * Validates IPN data.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @param array $data
     * @throws Exception
     */
    private function validateOrderData(G2A_Pay_Model_Order_Interface $order, array $data)
    {
        if ($order->getAmount() != $data['amount']) {
            throw new G2A_Pay_Exception_InvalidInput('Invalid order amount provided');
        }

        if ($order->getCurrency() != $data['currency']) {
            throw new G2A_Pay_Exception_InvalidInput('Invalid order currency provided');
        }

        if (!in_array($data['status'], static::$IPN_STATUSES)) {
            throw new G2A_Pay_Exception_InvalidInput('Unknown status provided');
        }
    }

    /**
     * Validates IPN hash.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @param $transactionId
     * @param $hash
     * @throws Exception
     */
    protected function validateOrderHash(G2A_Pay_Model_Order_Interface $order, $transactionId, $hash)
    {
        $validHash = $this->generateOrderHash($order, $transactionId);
        if ($hash !== $validHash) {
            throw new G2A_Pay_Exception_InvalidInput('Invalid hash provided');
        }
    }

    /**
     * Validate order.
     *
     * @param $order
     * @throws Exception
     */
    protected function validateOrder(G2A_Pay_Model_Order_Interface $order)
    {
        if ($order->isNew()) {
            throw new G2A_Pay_Exception_NotFound('Order does not exist');
        }
    }

    /**
     * Generates order hash to match IPN hash.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @param $transactionId
     * @return string
     */
    protected function generateOrderHash(G2A_Pay_Model_Order_Interface $order, $transactionId)
    {
        /** @var G2A_Pay_Helper_Utils $helper */
        $helper = Mage::helper('g2apay/utils');

        return $helper->hash($transactionId . $order->getId() . $order->getAmount() . $this->_config->getApiSecret());
    }

    /**
     * Updates order with IPN $data.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @param array $data
     */
    private function updateOrderData(G2A_Pay_Model_Order_Interface $order, array $data)
    {
        $order->setTransactionId($data['transactionId']);

        $helper = Mage::helper('g2apay');

        if ($data['status'] === 'complete') {
            $message = $helper->__('G2A Pay IPN update: payment complete');
            $order->complete($message, $this->_config->canSendCompleteEmail());
        } elseif ($data['status'] === 'partial_refunded' || $data['status'] === 'refunded') {
            $message = $helper->__('G2A Pay IPN update: payment refund by %.2f', $data['refundedAmount']);
            $order->refund($data['refundedAmount'], $message);
        } elseif ($data['status'] === 'rejected') {
            $message = $helper->__('G2A Pay IPN update: payment rejected');
            $order->reject($message);
        } elseif ($data['status'] === 'canceled') {
            $message = $helper->__('G2A Pay IPN update: payment cancelled');
            $order->cancel($message);
        }
    }
}
