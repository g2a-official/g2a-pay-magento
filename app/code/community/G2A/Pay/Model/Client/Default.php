<?php
/**
 * G2A Pay default request client.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Client_Default implements G2A_Pay_Model_Client_Interface
{
    /** @var resource */
    protected $_curl;

    /** @var  string */
    protected $_url;

    /** @var string */
    protected $_method;

    /** @var array */
    protected $_headers;

    /** @var  array|null */
    protected $_data;

    /**
     * Accepts required url param and optional method
     * Optionally can take data array param and headers array params.
     *
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->_url    = $params['url']; // @todo validate required url
        $this->_method = isset($params['method']) ? $params['method'] : static::METHOD_GET;

        if (isset($params['headers'])) {
            $this->_headers = (array) $params['headers'];
        }

        if (isset($params['data'])) {
            $this->_data = $params['data'];
        }
    }

    /**
     * Sends post request and return array data from response json.
     *
     * @param $data array|null
     * @return array
     * @throws Exception
     */
    public function request($data = null)
    {
        if ($data !== null) {
            $this->_data = $data;
        }

        $this->configure();
        $response = $this->execute();

        if (false === $response) {
            throw new G2A_Pay_Exception_Error('Request error: ' . $this->getLastError());
        }

        $result = json_decode($response, true);

        if (!is_array($result)) {
            throw new G2A_Pay_Exception_Error('Wrong response: ' . $result);
        }

        return $result;
    }

    /**
     * Init and return curl resource.
     *
     * @return resource
     */
    protected function resource()
    {
        if (is_null($this->_curl)) {
            $this->_curl = curl_init();
        }

        return $this->_curl;
    }

    /**
     * Set curl option.
     *
     * @param $option
     * @param null $value
     */
    protected function setOption($option, $value = null)
    {
        if (is_array($option)) {
            curl_setopt_array($this->resource(), $option);
        } else {
            curl_setopt($this->resource(), $option, $value);
        }
    }

    /**
     * Configure curl request method.
     */
    private function configureMethod()
    {
        if (static::METHOD_GET !== $this->_method) {
            if (static::METHOD_POST === $this->_method) {
                $this->setOption(CURLOPT_POST, 1);
            } elseif (in_array($this->_method, array(
                static::METHOD_PUT,
                static::METHOD_PATCH,
                static::METHOD_DELETE,
            ))) {
                $this->setOption(CURLOPT_CUSTOMREQUEST, $this->_method);
            }
        }
    }

    /**
     * Configure curl headers.
     */
    private function configureHeaders()
    {
        $headers = array();
        if (!empty($this->_headers)) {
            foreach ($this->_headers as $name => $header) {
                if (is_string($name)) {
                    $headers[] = "{$name}:{$header}";
                } else {
                    $headers[] = $header;
                }
            }
        }
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Configure curl request.
     */
    private function configureRequest()
    {
        $this->setOption(CURLOPT_URL, $this->_url);
        $this->setOption(CURLOPT_RETURNTRANSFER, 1);
    }

    /**
     * Configure curl data.
     */
    private function configureData()
    {
        if (!empty($this->_data) && in_array($this->_method, array(
                static::METHOD_POST,
                static::METHOD_PUT,
                static::METHOD_PATCH,
            ))
        ) {
            $data = is_array($this->_data) ? http_build_query($this->_data) : (string) $this->_data;
            $this->setOption(CURLOPT_POSTFIELDS, $data);
        }
    }

    /**
     * Configure curl.
     */
    protected function configure()
    {
        $this->configureRequest();
        $this->configureMethod();
        $this->configureHeaders();
        $this->configureData();
    }

    /**
     * Execute curl request.
     *
     * @return mixed
     */
    protected function execute()
    {
        return curl_exec($this->resource());
    }

    /**
     * Get last curl error.
     *
     * @return string|null
     */
    public function getLastError()
    {
        return curl_error($this->resource());
    }
}
