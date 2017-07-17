<?php
/**
 * Log levels options model.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Model_Option_Log
{
    /**
     * Returns log levels options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $levels = G2A_Pay_Model_Config::getLogLevels();

        return array_map(function ($level) {
            return array(
                'value' => $level,
                'label' => $level,
            );
        }, $levels);
    }
}
