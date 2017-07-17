<?php
/**
 * G2A Pay utilities helper.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Helper_Utils extends Mage_Core_Helper_Abstract
{
    /**
     * Calculate security hash.
     *
     * @param $string
     * @return string
     */
    public function hash($string)
    {
        return hash('sha256', $string);
    }

    /**
     * Get remote IP address.
     *
     * @return mixed
     */
    public function getRemoteIp()
    {
        return Mage::helper('core/http')->getRemoteAddr();
    }
}
