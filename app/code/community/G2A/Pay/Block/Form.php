<?php

/**
 * G2A Pay payment option form.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * G2A_Pay_Block_Form constructor
     */
    protected function _construct()
    {
        if (!Mage::getStoreConfig('payment/g2apay/use_title', Mage::app()->getStore())) {
            $mark = Mage::getConfig()->getBlockClassName('core/template/g2apay/logo.png');
            $mark = new $mark;
            $mark->setTemplate('g2apay/payment/option.phtml');
            $this
                ->setMethodTitle('')
                ->setMethodLabelAfterHtml($mark->toHtml());
        }

        parent::_construct();
    }
}
