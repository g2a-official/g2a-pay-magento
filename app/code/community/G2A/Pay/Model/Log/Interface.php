<?php
/**
 * G2A Pay log interface.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface G2A_Pay_Model_Log_Interface
{
    /**
     * Log message with default level.
     *
     * @param $data
     */
    public function message($data);

    /**
     * Log error message.
     *
     * @param $data
     */
    public function error($data);

    /**
     * Log any level message.
     *
     * @param $data
     * @param string $level
     */
    public function log($data, $level = 'message');

    /**
     * Check if logger is enabled for given level.
     *
     * @param $level
     * @return mixed
     */
    public function canLog($level);
}
