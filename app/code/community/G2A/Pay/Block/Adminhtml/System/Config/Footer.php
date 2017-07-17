<?php
/**
 * G2A Pay configuration footer block.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_Block_Adminhtml_System_Config_Footer extends G2A_Pay_Block_Adminhtml_System_Config_Row
{
    /**
     * Template path.
     *
     * @var string
     */
    protected $_template = 'g2apay/system/config/footer.phtml';

    /**
     * Return current IPN url.
     *
     * @return string
     */
    public function getIpnUrl()
    {
        /** @var G2A_Pay_Model_Factory $factory */
        $factory = Mage::getSingleton('g2apay/factory');
        $config  = $factory->getConfig();

        return Mage::getUrl('g2apay/ipn/update', array(
            'secret'  => $config->getIpnSecret(),
            '_secure' => true,
        ));
    }
}
