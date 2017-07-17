<?php
/**
 * G2A Pay log abstract.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class G2A_Pay_Model_Log_Abstract extends G2A_Pay_Model_Base implements G2A_Pay_Model_Log_Interface
{
    /**
     * Check if logging is enabled.
     *
     * @param $level
     * @return bool
     */
    public function canLog($level)
    {
        return $this->_config->isLogEnabled() && $this->hasLevel($level);
    }

    /**
     * Check if logging level is enabled.
     *
     * @param $level
     * @return bool
     */
    protected function hasLevel($level)
    {
        return $this->_config->hasLogLevel($level);
    }
}
