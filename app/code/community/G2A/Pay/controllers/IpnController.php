<?php
/**
 * G2A Pay IPN method controller.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class G2A_Pay_IpnController extends G2A_Pay_Controller_Base
{
    /**
     * Action triggered by G2A Pay IPN.
     */
    public function updateAction()
    {
        $data = $this->getIpnData();

        try {
            $model = Mage::getModel('g2apay/ipn');
            $this->validateIpnSecret($model->getIpnSecret());
            $model->processIpnData($data);
            $this->logUpdate($this->__('IPN update success'), $data);
            echo 'OK';
        } catch (G2A_Pay_Exception_InvalidInput $e) {
            $this->logError($e->getMessage(), $this->__('IPN invalid input'), $data);
            $this->show400($this->__($e->getMessage()));
        } catch (G2A_Pay_Exception_NotFound $e) {
            $this->logError($e->getMessage(), $this->__('IPN wrong access'), $data);
            $this->show404($this->__($e->getMessage()));
        } catch (Exception $e) {
            $this->logError($e->getMessage(), $this->__('IPN common error'), $data);
            $this->show500($this->__('Something went wrong'));
        }
    }

    /**
     * Validate optional IPN url secret.
     *
     * @param $secret
     * @throws Exception
     */
    protected function validateIpnSecret($secret)
    {
        if (!empty($secret) && !$this->getRequest()->has($secret)) {
            throw new G2A_Pay_Exception_NotFound('Invalid IPN url');
        }
    }

    /**
     * Get IPN data from request.
     *
     * @return array
     */
    protected function getIpnData()
    {
        return $this->getRequest()->getPost();
    }
}
