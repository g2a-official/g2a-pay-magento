<?php
/**
 * Base class to send REST requests to G2A Pay.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class G2A_Pay_Model_Rest extends G2A_Pay_Model_Base
{
    protected $_method = G2A_Pay_Model_Client_Interface::METHOD_GET;
    protected $_path   = '/';

    /**
     * REST Call to G2A Pay.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @param array $data
     * @return array
     */
    public function call(G2A_Pay_Model_Order_Interface $order, $data = array())
    {
        $client   = $this->getClient($this->getUrl($order));
        $response = $client->request($this->getData($order, $data));
        $this->validateResponseData($response);

        return $response;
    }

    /**
     * Create REST Client for current configuration.
     *
     * @param $url string
     * @return G2A_Pay_Model_Client_Interface
     */
    protected function getClient($url)
    {
        $params = array(
            'url'     => $url,
            'method'  => $this->_method,
            'headers' => $this->getHeaders(),
        );

        return Mage::getModel('g2apay/client_default', $params);
    }

    /**
     * Generate full REST url and translates path placeholders.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @return string
     */
    protected function getUrl(G2A_Pay_Model_Order_Interface $order)
    {
        $path = $this->_config->getRestUrl($this->_path);

        return strtr($path, array(
            '{transaction_id}' => (string) $order->getTransactionId(),
        ));
    }

    /**
     * Generate REST call headers array.
     *
     * @return array
     */
    protected function getHeaders()
    {
        $authorization = $this->_config->getApiHash() . ';' . $this->_config->getAuthorizationHash();

        return array(
            'Authorization' => $authorization,
        );
    }

    /**
     * Generate all necessary data to attach to request payload
     * Or null if not data should be sent.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @param array $additional
     * @return array|null
     */
    abstract protected function getData(G2A_Pay_Model_Order_Interface $order, $additional = array());

    /**
     * Validate REST call response data
     * Should throw exception on fail.
     *
     * @throws Exception
     * @param $data
     */
    abstract protected function validateResponseData($data);
}
