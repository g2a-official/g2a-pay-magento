<?php
/**
 * G2A Pay order helper.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Helper_Order extends Mage_Core_Helper_Abstract
{
    /**
     * Round order price.
     *
     * @param $price
     * @return float
     */
    public function roundPrice($price)
    {
        return Mage::app()->getStore()->roundPrice($price);
    }

    /**
     * Return order price always with two decimal places.
     *
     * @param $price
     * @return string
     */
    public function roundToTwoDecimal($price)
    {
        return number_format((float) $price, 2, '.', '');
    }
}
