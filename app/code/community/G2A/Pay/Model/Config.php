<?php
/**
 * G2A Pay configuration wrapper.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Config
{
    /**
     * Default fallback environment.
     */
    const DEFAULT_ENVIRONMENT = 'production';

    /**
     * @var array allowed environments
     */
    private static $ENVIRONMENTS = array('production', 'sandbox');

    /**
     * @var array allowed log levels
     */
    private static $LOG_LEVELS = array('message', 'error');

    /**
     * @var array gateway urls grouped by environment
     */
    private static $GATEWAY_URLS = array(
        'production' => 'https://checkout.pay.g2a.com/index/gateway',
        'sandbox'    => 'https://checkout.test.pay.g2a.com/index/gateway',
    );

    /**
     * @var array create quote urls grouped by environment
     */
    private static $QUOTE_URLS = array(
        'production' => 'https://checkout.pay.g2a.com/index/createQuote',
        'sandbox'    => 'https://checkout.test.pay.g2a.com/index/createQuote',
    );

    /**
     * @var array REST base urls grouped by environment
     */
    private static $REST_BASE_URLS = array(
        'production' => 'https://pay.g2a.com/rest',
        'sandbox'    => 'https://www.test.pay.g2a.com/rest',
    );

    private $_environment;
    private $_apiHash;
    private $_apiSecret;
    private $_merchantEmail;
    private $_ipnSecret;
    private $_enableLog;
    private $_logLevels;
    private $_completeEmail;

    /**
     * Gets available environments array.
     *
     * @return array
     */
    public static function getEnvironments()
    {
        return static::$ENVIRONMENTS;
    }

    /**
     * Gets available log levels array.
     *
     * @return array
     */
    public static function getLogLevels()
    {
        return static::$LOG_LEVELS;
    }

    /**
     * Params must contain api_hash, api_secret and merchant_email
     * Optionally it can take environment which will be filtered by
     * Allowed array and fallen back to default one.
     *
     * @param array $params
     */
    public function __construct($params = array())
    {
        if (array_key_exists('environment', $params) && in_array($params['environment'], static::$ENVIRONMENTS)) {
            $this->_environment = $params['environment'];
        } else {
            $this->_environment = static::DEFAULT_ENVIRONMENT;
        }

        $this->_enableLog = isset($params['enable_log']) ? (bool) $params['enable_log'] : false;

        if (array_key_exists('log_levels', $params)) {
            $this->_logLevels = array_intersect(static::$LOG_LEVELS, (array) $params['log_levels']);
        } else {
            $this->_logLevels = array();
        }

        $this->_completeEmail = isset($params['complete_email']) ? (bool) $params['complete_email'] : false;

        $this->_apiHash       = $params['api_hash'];
        $this->_apiSecret     = $params['api_secret'];
        $this->_merchantEmail = $params['merchant_email'];
        $this->_ipnSecret     = $params['ipn_secret'];
    }

    /**
     * Returns current environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }

    /**
     * Returns current API Secret.
     *
     * @return string
     */
    public function getApiSecret()
    {
        return $this->_apiSecret;
    }

    /**
     * Returns current API Hash.
     *
     * @return mixed
     */
    public function getApiHash()
    {
        return $this->_apiHash;
    }

    /**
     * Returns generated authorization hash.
     *
     * @return string
     */
    public function getAuthorizationHash()
    {
        /** @var G2A_Pay_Helper_Utils $helper */
        $helper = Mage::helper('g2apay/utils');

        return $helper->hash($this->_apiHash . $this->_merchantEmail . $this->_apiSecret);
    }

    /**
     * Get ipn secret token if is set.
     *
     * @return string|null
     */
    public function getIpnSecret()
    {
        return $this->_ipnSecret;
    }

    /**
     * Returns success redirect url.
     *
     * @param array $params
     * @return string
     */
    public function getSuccessUrl($params = array())
    {
        return Mage::getUrl('g2apay/gateway/success', $params);
    }

    /**
     * Returns failure redirect url.
     *
     * @param array $params
     * @return string
     */
    public function getFailureUrl($params = array())
    {
        return Mage::getUrl('g2apay/gateway/failure', $params);
    }

    /**
     * Returns Create Quote url dependent on current environment.
     *
     * @return string
     */
    public function getCreateQuoteUrl()
    {
        return static::$QUOTE_URLS[$this->_environment];
    }

    /**
     * Returns Gateway url dependent on current environment
     * With additional $token.
     *
     * @param string $token
     * @return string
     */
    public function getGatewayUrl($token)
    {
        $baseUrl = static::$GATEWAY_URLS[$this->_environment];

        return $baseUrl . '?' . http_build_query(compact('token'));
    }

    /**
     * Returns REST url dependent on current environment
     * With optional $path appended.
     *
     * @param string $path
     * @return string
     */
    public function getRestUrl($path = '/')
    {
        $baseUrl = static::$REST_BASE_URLS[$this->_environment];

        return $baseUrl . $path;
    }

    /**
     * Check if log is enabled.
     *
     * @return bool
     */
    public function isLogEnabled()
    {
        return $this->_enableLog;
    }

    /**
     * Check if given log level is active.
     *
     * @param $level
     * @return bool
     */
    public function hasLogLevel($level)
    {
        return in_array($level, $this->_logLevels);
    }

    /**
     * Check if complete email can be send.
     *
     * @return bool
     */
    public function canSendCompleteEmail()
    {
        return $this->_completeEmail;
    }
}
