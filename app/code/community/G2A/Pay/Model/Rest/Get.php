<?php
/**
 * Sends get REST requests to G2A Pay.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Rest_Get extends G2A_Pay_Model_Rest
{
    protected $_method = G2A_Pay_Model_Client_Interface::METHOD_GET;
    protected $_path   = '/transactions/{transaction_id}';

    /**
     * Return request data.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @param array $additional
     * @return null
     */
    protected function getData(G2A_Pay_Model_Order_Interface $order, $additional = array())
    {
        return;
    }

    /**
     * Validate response data.
     *
     * @param $data
     * @throws Exception
     */
    protected function validateResponseData($data)
    {
        if (empty($data['transactionId'])) {
            throw new G2A_Pay_Exception_Error('Get order operation failed');
        }
    }
}
