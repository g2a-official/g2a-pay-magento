<?php
/**
 * G2A Pay Base abstract model.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class G2A_Pay_Model_Base
{
    /** @var  G2A_Pay_Model_Config */
    protected $_config;

    /**
     * Constructor.
     */
    public function __construct()
    {
        /** @var G2A_Pay_Model_Factory $factory */
        $factory       = Mage::getSingleton('g2apay/factory');
        $this->_config = $factory->getConfig();
    }
}
