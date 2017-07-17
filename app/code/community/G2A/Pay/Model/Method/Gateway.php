<?php

/**
 * G2A Pay Gateway method payment module.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Method_Gateway extends Mage_Payment_Model_Method_Abstract
{
    const METHOD_CODE = 'g2apay';

    protected $_code                    = self::METHOD_CODE;
    protected $_formBlockType           = 'g2apay/form';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canCapturePartial       = false;

    /**
     * Config wrapper instance.
     * @var G2A_Pay_Model_Config
     */
    protected $_config;

    /**
     * Constructor.
     */
    public function __construct()
    {
        /** @var G2A_Pay_Model_Factory $factory */
        $factory       = Mage::getSingleton('g2apay/factory');
        $this->_config = $factory->getConfig();
        parent::__construct();
    }

    /**
     * Return url for redirection after order placed.
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('g2apay/gateway/redirect', array('_secure' => true));
    }

    /**
     * Refund capture.
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return G2A_Pay_Model_Method_Gateway
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $order = Mage::getModel('g2apay/order_default', $payment->getOrder());
        $this->refundOrder($order, $amount);

        return $this;
    }

    /**
     * Process success request.
     *
     * @param $data
     */
    public function processSuccessRequest($data)
    {
        $this->validateOrderSecureData($data);
        $this->clearOrderSecureData();
        $this->clearPaymentData();
    }

    /**
     * Process failure request.
     *
     * @param $data
     */
    public function processFailureRequest($data)
    {
        $this->validateOrderSecureData($data);
        $order = $this->getCustomerOrderById($data['order_id']);
        $order->cancel('Order cancelled by customer');
        $this->clearOrderSecureData();
        $this->clearPaymentData();
    }

    /**
     * @param $orderId
     * @return G2A_Pay_Model_Order_Default
     * @throws G2A_Pay_Exception_Forbidden
     * @throws G2A_Pay_Exception_NotFound
     */
    public function getCustomerOrderById($orderId)
    {
        if (is_null($orderId)) {
            throw new G2A_Pay_Exception_NotFound('Missing order id');
        }

        /** @var G2A_Pay_Model_Factory $factory */
        $factory = Mage::getSingleton('g2apay/factory');
        $order   = $factory->getOrderById($orderId);

        if ($order->isNew()) {
            throw new G2A_Pay_Exception_NotFound('Order not found');
        }

        $customerId = $this->getCustomerId();
        $ownerId    = $order->getOwnerId();
        if (
            (null !== $customerId || null !== $ownerId) // guest order
            && ($customerId !== $ownerId)
        ) { // is order owner
            throw new G2A_Pay_Exception_Forbidden('Customer is not order owner');
        }

        return $order;
    }

    /**
     * Get current customer id.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return Mage::getSingleton('customer/session')->getCustomerId();
    }

    /**
     * Retrieves G2A Pay Checkout token for given $order
     * And generates Checkout redirect url.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @return string
     * @throws Exception
     */
    public function getCreateQuoteRedirect(G2A_Pay_Model_Order_Interface $order)
    {
        $this->validateOrderPayment($order);
        $request = $this->getCreateQuoteRequest($order);
        $data    = $request->request();
        $this->validateCreateQuoteResponseData($data);

        return $this->_config->getGatewayUrl($data['token']);
    }

    /**
     * Get G2A Pay Create quote request for given $order.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @return G2A_Pay_Model_Client_Interface
     */
    public function getCreateQuoteRequest(G2A_Pay_Model_Order_Interface $order)
    {
        $url         = $this->_config->getCreateQuoteUrl();
        $secureToken = $this->generateSecureToken();
        $this->storeOrderSecureData($order, $secureToken);
        $data   = $this->getCreateQuoteData($order, $secureToken);
        $method = G2A_Pay_Model_Client_Interface::METHOD_POST;

        $client = Mage::getModel('g2apay/client_default', compact('url', 'method', 'data'));

        return $client;
    }

    /**
     * Prepares and returns G2A Pay Create quote data for given $order.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @param string $secureToken
     * @return array
     */
    protected function getCreateQuoteData(G2A_Pay_Model_Order_Interface $order, $secureToken)
    {
        $urlData = array(
            'order_id' => $order->getId(),
            'token'    => $secureToken,
        );

        return array(
            'api_hash'    => $this->_config->getApiHash(),
            'hash'        => $this->generateOrderHash($order),
            'order_id'    => $order->getId(),
            'email'       => $order->getOwnerEmail(),
            'amount'      => $order->getAmount(),
            'currency'    => $order->getCurrency(),
            'items'       => $this->getCreateQuoteItemsData($order->getItems()),
            'description' => '',
            'url_failure' => $this->_config->getFailureUrl($urlData),
            'url_ok'      => $this->_config->getSuccessUrl($urlData),
            'addresses'   => $this->generateAddressesArray($order),
        );
    }

    /**
     * @param G2A_Pay_Model_Order_Interface $orderInterface
     * @return array
     */
    protected function generateAddressesArray(G2A_Pay_Model_Order_Interface $orderInterface)
    {
        $addresses = array();
        /** @var Mage_Sales_Model_Order $order */
        $order           = Mage::getModel('sales/order')->loadByIncrementId($orderInterface->getId());
        $billingAddress  = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress() ? $order->getShippingAddress() : $billingAddress;

        $addresses['billing'] = array(
            'firstname' => $billingAddress->getFirstname(),
            'lastname'  => $billingAddress->getLastname(),
            'line_1'    => $billingAddress->getStreet1(),
            'line_2'    => $billingAddress->getStreet2(),
            'zip_code'  => $billingAddress->getPostcode(),
            'city'      => $billingAddress->getCity(),
            'company'   => is_null($billingAddress->getCompany()) ? '' : $billingAddress->getCompany(),
            'county'    => $billingAddress->getRegion(),
            'country'   => $billingAddress->getCountryId(),
        );
        $addresses['shipping'] = array(
            'firstname' => $shippingAddress->getFirstname(),
            'lastname'  => $shippingAddress->getLastname(),
            'line_1'    => $shippingAddress->getStreet1(),
            'line_2'    => $shippingAddress->getStreet2(),
            'zip_code'  => $shippingAddress->getPostcode(),
            'city'      => $shippingAddress->getCity(),
            'company'   => is_null($shippingAddress->getCompany()) ? '' : $shippingAddress->getCompany(),
            'county'    => $shippingAddress->getRegion(),
            'country'   => $shippingAddress->getCountryId(),
        );

        return $addresses;
    }

    /**
     * @return string
     */
    protected function generateSecureToken()
    {
        /** @var $helper Mage_Core_Helper_Data */
        $helper       = Mage::helper('core');
        $randomString = md5($helper->getRandomString(32) . time());

        return $randomString;
    }

    /**
     * Send refund request to G2A Pay.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @param $amount
     */
    protected function refundOrder(G2A_Pay_Model_Order_Interface $order, $amount)
    {
        try {
            /** @var G2A_Pay_Model_Rest $api */
            $api = Mage::getModel('g2apay/rest_refund');
            $api->call($order, compact('amount'));
        } catch (Exception $e) {
            $helper = Mage::helper('g2apay');
            Mage::throwException($helper->__('Remote order refund failed'));
        }
    }

    /**
     * Maps order items array into data
     * To use with G2A Pay Create quote data.
     *
     * @param array $items
     * @return array
     */
    protected function getCreateQuoteItemsData(array $items)
    {
        $data = array();

        /** @var G2A_Pay_Model_Order_Item_Interface $item */
        foreach ($items as $item) {
            $data[] = array(
                'qty'    => $item->getQuantity(),
                'name'   => $item->getName(),
                'sku'    => $item->getSku(),
                'amount' => $item->getAmount(),
                'type'   => $item->getType(),
                'id'     => $item->getID(),
                'price'  => $item->getPrice(),
                'url'    => $item->getUrl(),
            );
        }

        return $data;
    }

    /**
     * Validates if G2A Pay Create quote response data status.
     *
     * @param $data
     * @throws Exception
     */
    protected function validateCreateQuoteResponseData($data)
    {
        if (!isset($data['status']) || strcasecmp('ok', $data['status']) !== 0) {
            throw new G2A_Pay_Exception_InvalidInput('Wrong data returned from server');
        }
    }

    /**
     * Generates API hash for given $order
     * To use with G2A Pay create quote request.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @return string
     */
    protected function generateOrderHash(G2A_Pay_Model_Order_Interface $order)
    {
        /** @var G2A_Pay_Helper_Utils $helper */
        $helper = Mage::helper('g2apay/utils');

        return $helper->hash($order->getId() . $order->getAmount() . $order->getCurrency() . $this->_config->getApiSecret());
    }

    /**
     * @param G2A_Pay_Model_Order_Interface $order
     * @throws Exception
     */
    protected function validateOrderPayment(G2A_Pay_Model_Order_Interface $order)
    {
        if ($order->getPaymentMethodCode() !== $this->_code) {
            throw new G2A_Pay_Exception_Forbidden('Invalid payment method');
        }
    }

    /**
     * Store order secure data to math against on return.
     *
     * @param G2A_Pay_Model_Order_Interface $order
     * @param $secureToken
     */
    protected function storeOrderSecureData(G2A_Pay_Model_Order_Interface $order, $secureToken)
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setG2apayToken($secureToken);
        $session->setG2apayOrderId($order->getId());
    }

    /**
     * Validates order secure data.
     *
     * @param array $data
     * @throws Exception
     */
    protected function validateOrderSecureData($data)
    {
        $data    = (array) $data;
        $session = Mage::getSingleton('checkout/session');
        $token   = $session->getG2apayToken();
        $orderId = $session->getG2apayOrderId();

        if (!isset($data['order_id']) || empty($data['order_id']) || $orderId != $data['order_id']) {
            throw new G2A_Pay_Exception_InvalidInput('Unknown order');
        }

        if (!isset($data['token']) || empty($data['token']) || $token != $data['token']) {
            throw new G2A_Pay_Exception_InvalidInput('Invalid token');
        }
    }

    /**
     * Clears order secure data.
     */
    protected function clearOrderSecureData()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->unsG2apayToken();
        $session->unsG2apayOrderId();
    }

    /**
     * Clears payment required session data.
     */
    protected function clearPaymentData()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->unsLastRealOrderId();
    }
}
