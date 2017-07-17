<?php
/**
 * Sends refund REST requests to G2A Pay.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Rest_Refund extends G2A_Pay_Model_Rest
{
    protected $_method = G2A_Pay_Model_Client_Interface::METHOD_PUT;
    protected $_path   = '/transactions/{transaction_id}';

    /**
     * @param G2A_Pay_Model_Order_Interface $order
     * @param array $additional
     * @return array
     */
    protected function getData(G2A_Pay_Model_Order_Interface $order, $additional = array())
    {
        $helper = Mage::helper('g2apay/order');
        $amount = $helper->roundPrice($additional['amount']);

        $data = array(
            'action' => 'refund',
            'amount' => $amount,
            'hash'   => $this->generateHash($order, $amount),
        );

        return $data;
    }

    /**
     * @param $data
     * @throws G2A_Pay_Exception_Error
     */
    protected function validateResponseData($data)
    {
        if (!isset($data['status']) || strcasecmp($data['status'], 'ok') !== 0) {
            throw new G2A_Pay_Exception_Error('Refund operation failed');
        }
    }

    /**
     * Generates current request hash.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @param $refund
     * @return string
     */
    protected function generateHash(G2A_Pay_Model_Order_Interface $order, $refund)
    {
        /** @var G2A_Pay_Helper_Utils $helper */
        $helper      = Mage::helper('g2apay/utils');
        $orderHelper = Mage::helper('g2apay/order');

        return $helper->hash($order->getTransactionId()
            . $order->getId()
            . $orderHelper->roundToTwoDecimal($order->getAmount())
            . $orderHelper->roundToTwoDecimal($refund)
            . $this->_config->getApiSecret());
    }
}
