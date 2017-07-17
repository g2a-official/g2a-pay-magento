<?php
/**
 * G2A Pay factory class
 * Creates object with dependencies.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Factory
{
    /**
     * @var G2A_Pay_Model_Config|null
     */
    protected $_config;

    /**
     * Current G2A Pay config wrapper instance.
     *
     * @return G2A_Pay_Model_Config
     */
    public function getConfig()
    {
        if (is_null($this->_config)) {
            $params = array(
                'environment'    => static::getStoreConfigValue('environment'),
                'api_hash'       => static::getStoreConfigValue('api_hash'),
                'api_secret'     => static::getStoreConfigValue('api_secret'),
                'merchant_email' => static::getStoreConfigValue('merchant_email'),
                'ipn_secret'     => static::getStoreConfigValue('ipn_secret'),
                'log_levels'     => explode(',', (string) static::getStoreConfigValue('log_levels')),
                'complete_email' => (bool) static::getStoreConfigValue('complete_email'),
                'enable_log'     => (bool) static::getStoreConfigValue('enable_log'),
            );

            $this->_config = Mage::getSingleton('g2apay/config', $params);
        }

        return $this->_config;
    }

    /**
     * Factory to get order by id.
     *
     * @param $id
     * @return G2A_Pay_Model_Order_Default
     */
    public function getOrderById($id)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($id);

        return Mage::getModel('g2apay/order_default', $order);
    }

    /**
     * Get current logger.
     *
     * @return G2A_Pay_Model_Log_Interface
     */
    public function getLog()
    {
        return Mage::getModel('g2apay/log_file');
    }

    /**
     * Get module configuration option value for give $name.
     *
     * @param $name
     * @return mixed
     */
    protected static function getStoreConfigValue($name)
    {
        return Mage::getStoreConfig(
            "payment/g2apay/{$name}",
            Mage::app()->getStore()
        );
    }
}
