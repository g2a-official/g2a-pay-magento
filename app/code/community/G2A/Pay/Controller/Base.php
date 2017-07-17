<?php
/**
 * G2A Pay Base controller.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class G2A_Pay_Controller_Base extends Mage_Core_Controller_Front_Action
{
    /** @var  G2A_Pay_Model_Log_Interface */
    protected $_log;

    /**
     * Bad Request page.
     *
     * @param null $message
     */
    protected function show400($message = null)
    {
        $this->_redirectUrl(Mage::getBaseUrl());
    }

    /**
     * Forbidden page.
     *
     * @param string|null $message
     */
    protected function show403($message = null)
    {
        $this->_redirectUrl(Mage::getBaseUrl());
    }

    /**
     * Not Found page.
     *
     * @param string|null $message
     */
    protected function show404($message = null)
    {
        $this->norouteAction();
    }

    /**
     * Error page.
     *
     * @param string|null $message
     * @throws Mage_Core_Exception
     */
    protected function show500($message = null)
    {
        Mage::throwException($message);
    }

    /**
     * Log message data.
     *
     * @param $update
     * @param $input
     */
    protected function logUpdate($update, $input)
    {
        $log = $this->getLog();
        if ($log->canLog('message')) {
            /** @var G2A_Pay_Helper_Utils $helper */
            $helper = Mage::helper('g2apay/utils');
            $data   = array(
                'update' => $update,
                'input'  => $input,
                'url'    => $this->getRequest()->getRequestUri(),
                'ip'     => $helper->getRemoteIp(),
            );
            $this->getLog()->message($data);
        }
    }

    /**
     * Log error data.
     *
     * @param $error
     * @param $description
     * @param $input
     */
    protected function logError($error, $description, $input)
    {
        $log = $this->getLog();
        if ($log->canLog('error')) {
            /** @var G2A_Pay_Helper_Utils $helper */
            $helper = Mage::helper('g2apay/utils');
            $data   = array(
                'exception'   => $error,
                'description' => $description,
                'input'       => $input,
                'url'         => $this->getRequest()->getRequestUri(),
                'ip'          => $helper->getRemoteIp(),
            );
            $log->error($data);
        }
    }

    /**
     * Get logger.
     *
     * @return G2A_Pay_Model_Log_Interface
     */
    protected function getLog()
    {
        if (is_null($this->_log)) {
            /** @var G2A_Pay_Model_Factory $factory */
            $factory    = Mage::getSingleton('g2apay/factory');
            $this->_log = $factory->getLog();
        }

        return $this->_log;
    }
}
