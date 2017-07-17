<?php
/**
 * G2A Pay Mage_Sales_Model_Order order wrapper.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Order_Default implements G2A_Pay_Model_Order_Interface
{
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
     * Get order id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_order->getIncrementId();
    }

    /**
     * Get order currency symbol.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_order->getOrderCurrencyCode();
    }

    /**
     * Get order amount.
     *
     * @return float
     */
    public function getAmount()
    {
        /** @var G2A_Pay_Helper_Order $helper */
        $helper = Mage::helper('g2apay/order');

        return $helper->roundPrice($this->_order->getGrandTotal());
    }

    /**
     * Get order owner email.
     *
     * @return string
     */
    public function getOwnerEmail()
    {
        return $this->_order->getCustomerEmail();
    }

    /**
     * Get order owner id.
     *
     * @return int
     */
    public function getOwnerId()
    {
        return $this->_order->getCustomerId();
    }

    /**
     * Get order items array.
     *
     * @return array
     */
    public function getItems()
    {
        $items = array_map(function ($item) {
            return Mage::getModel('g2apay/order_item_default', $item);
        }, $this->_order->getAllVisibleItems());

        /** @var G2A_Pay_Model_Order_Item_Interface $shipping */
        $shipping = Mage::getModel('g2apay/order_item_shipment', $this->_order);
        if ($shipping->getAmount() != 0) {
            $items[] = $shipping;
        }

        /** @var G2A_Pay_Model_Order_Item_Interface $discount */
        $discount = Mage::getModel('g2apay/order_item_discount', $this->_order);
        if ($discount->getAmount() != 0) {
            $items[] = $discount;
        }

        $itemsTotal = array_reduce($items, array($this, 'addItemAmount'), 0);

        $totalsDiff = $this->getAmount() - $itemsTotal;
        if (abs($totalsDiff) > 0.0001) {
            /** @var G2A_Pay_Model_Order_Item_Interface $other */
            $other = Mage::getModel('g2apay/order_item_other', $totalsDiff);
            if ($other->getAmount() != 0) {
                $items[] = $other;
            }
        }

        return $items;
    }

    /**
     * @param float $current
     * @param G2A_Pay_Model_Order_Item_Interface $item
     * @return mixed
     */
    protected function addItemAmount($current, $item)
    {
        return $current += $item->getAmount();
    }

    /**
     * Get order payment method code.
     *
     * @return string
     */
    public function getPaymentMethodCode()
    {
        $payment = $this->_order->getPayment();

        return $payment->getMethod();
    }

    /**
     * Check if order is new.
     *
     * @return bool
     */
    public function isNew()
    {
        return is_null($this->_order->getId());
    }

    /**
     * Set order transaction id.
     *
     * @param $id string
     */
    public function setTransactionId($id)
    {
        $payment = $this->_order->getPayment();
        $payment->setTransactionId($id);
    }

    /**
     * Get last order transaction id.
     *
     * @return string|null
     */
    public function getTransactionId()
    {
        $payment = $this->_order->getPayment();

        return $payment->getLastTransId();
    }

    /**
     * Complete current order.
     *
     * @param string $message
     * @param bool $notify
     * @throws Exception
     */
    public function complete($message = '', $notify = false)
    {
        $payment = $this->_order->getPayment();
        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT, null, false, $message);

        $payment
            ->setIsTransactionApproved(true)
            ->setShouldCloseParentTransaction(true)
            ->setIsTransactionClosed(1);

        if ($this->_order->canInvoice()) {
            $invoice = $this->_order->prepareInvoice();
            $invoice->register();
            $invoice->setTransactionId($payment->getLastTransId());
            $this->_order->addRelatedObject($invoice);
            $this->_order->save();

            if ($notify && !$this->_order->getEmailSent()) {
                $this->_order->queueNewOrderEmail()->addStatusHistoryComment(
                    Mage::helper('g2apay')->__('Invoice notification #%s', $invoice->getIncrementId())
                )
                    ->setIsCustomerNotified(true)
                    ->save();
            }
        } else {
            $this->_order->save();
        }
    }

    /**
     * Refund current order.
     *
     * @param $refund
     * @param string $message
     */
    public function refund($refund, $message = '')
    {
        // Refund confirmation transaction
        // Online refund should be done first
        $payment = $this->_order->getPayment();
        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, null, false, $message);

        $this->_order->save();
    }

    /**
     * Cancel current order.
     *
     * @param string $message
     * @throws Exception
     */
    public function cancel($message = '')
    {
        $this->_order->registerCancellation($message, false);
        $this->_order->save();
    }

    /**
     * Reject current order.
     *
     * @param string $message
     * @throws Exception
     */
    public function reject($message = '')
    {
        $payment = $this->_order->getPayment();

        $payment->setNotificationResult(true);
        $payment->setIsTransactionClosed(true);
        $payment->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_DENY, false);

        if (!empty($message)) {
            $this->_order->addStatusHistoryComment($message);
        }

        $this->_order->save();
    }

    /**
     * Get order debug log info.
     *
     * @return string
     */
    public function getLog()
    {
        return $this->_order->debug();
    }
}
