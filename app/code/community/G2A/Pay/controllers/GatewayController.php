<?php

/**
 * G2A Pay Gateway method controller.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_GatewayController extends G2A_Pay_Controller_Base
{
    /**
     * @var G2A_Pay_Model_Method_Gateway
     */
    protected $_model;

    /**
     * @var bool
     * @todo not available yet
     */
    protected $_allowRequestOrderId = false;

    /**
     * Method executed by parent constructor
     * This is not constructor! (See single '_').
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_model = Mage::getModel('g2apay/method_gateway');
    }

    /**
     * Action after processing order
     * Generate order token and redirect to G2A Pay Gateway.
     */
    public function redirectAction()
    {
        $order = null;

        try {
            $order    = $this->getRequestOrder();
            $redirect = $this->_model->getCreateQuoteRedirect($order);
            $this->logUpdate($this->__('Gateway will redirect to %s', $redirect),
                array('order' => $order->getLog(), 'customer_id' => $this->_model->getCustomerId())
            );
            $this->_redirectUrl($redirect);
        } catch (G2A_Pay_Exception_Forbidden $e) {
            $this->logGatewayException($e, $this->__('Gateway access forbidden'), $order);
            $this->show403($this->__('Order access forbidden'));
        } catch (G2A_Pay_Exception_NotFound $e) {
            $this->logGatewayException($e, $this->__('Gateway wrong order'), $order);
            $this->show404();
        } catch (Exception $e) {
            $this->logGatewayException($e, $this->__('Gateway common error'), $order);
            $this->show500($this->__('Something went wrong'));
        }
    }

    /**
     * Action after successful G2A Pay Checkout request.
     */
    public function successAction()
    {
        $checkout = Mage::getSingleton('checkout/session');
        try {
            $data = $this->getRequest()->getParams();
            $this->_model->processSuccessRequest($data);
            $this->_redirect('checkout/onepage/success', array('_secure' => true));
        } catch (Exception $e) {
            $checkout->addNotice($this->__('Something went wrong'));
            $this->_redirect('checkout/cart');
        }
    }

    /**
     * Action after failed or cancelled G2A Pay Checkout request.
     */
    public function failureAction()
    {
        $checkout = Mage::getSingleton('checkout/session');
        try {
            $data = $this->getRequest()->getParams();
            $this->_model->processFailureRequest($data);
            $checkout->addSuccess($this->__('Order cancelled'));
            $this->_redirect('checkout/cart');
        } catch (Exception $e) {
            $checkout->addError($this->__('Order cancel failed'));
            $this->_redirect('checkout/cart');
        }
    }

    /**
     * Helper model binding method to retrieve current order
     * Order can be passed as param id or can be taken form session.
     *
     * @throws Exception
     * @return G2A_Pay_Model_Order_Interface
     */
    protected function getRequestOrder()
    {
        $orderId = $this->getRequestOrderId();

        $order = $this->_model->getCustomerOrderById($orderId);

        return $order;
    }

    /**
     * Get current order id.
     *
     * @return mixed
     */
    protected function getRequestOrderId()
    {
        $orderId = $this->_allowRequestOrderId ? $this->getRequest()->getParam('order') : null;

        if (is_null($orderId)) {
            $session = Mage::getSingleton('checkout/session');
            $orderId = $session->getLastRealOrderId();
        }

        return $orderId;
    }

    /**
     * Gateway exception log method.
     *
     * @param Exception $exception
     * @param $message
     * @param G2A_Pay_Model_Order_Interface $order
     */
    protected function logGatewayException(Exception $exception, $message, G2A_Pay_Model_Order_Interface $order = null)
    {
        $this->logError($exception->getMessage(), $message,
            array(
                'order_id'    => $this->getRequestOrderId(),
                'customer_id' => $this->_model->getCustomerId(),
                'order'       => is_null($order) ? null : $order->getLog(),
            )
        );
    }
}
