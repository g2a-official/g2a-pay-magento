<?php
/**
 * G2A Pay file log.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Log_File extends G2A_Pay_Model_Log_Abstract
{
    /** Logs file format */
    const LOG_FILE_FORMAT = 'payment_g2apay_{level}.log';

    /**
     * Log message.
     *
     * @param $data
     */
    public function message($data)
    {
        $this->log($data, 'message');
    }

    /**
     * Log error.
     *
     * @param $data
     */
    public function error($data)
    {
        $this->log($data, 'error');
    }

    /**
     * Log data with given $level.
     *
     * @param $data
     * @param string $level
     */
    public function log($data, $level = 'message')
    {
        if ($this->canLog($level)) {
            $file = strtr(self::LOG_FILE_FORMAT,
                array('{level}' => $level)
            );
            Mage::getModel('core/log_adapter', $file)->log($data);
        }
    }
}
