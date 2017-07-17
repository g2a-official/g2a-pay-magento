<?php
/**
 * G2A Pay abstract configuration block.
 *
 * @category    G2A
 * @package     G2A_Pay
 * @author      G2A Team
 * @copyright   Copyright (c) 2015 G2A.COM
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class G2A_Pay_Block_Adminhtml_System_Config_Row extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Render html.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     * @throws Exception
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $columns = ($this->getRequest()->getParam('website') || $this->getRequest()->getParam('store')) ? 5 : 4;

        //method _decorateRowHtml does not exist in magento older than 1.7
        if (function_exists('_decorateRowHtml')) {
            return $this->_decorateRowHtml($element, '<td colspan="' . $columns . '">' . $this->toHtml() . '</td>');
        }

        return '<td colspan="' . $columns . '">' . $this->toHtml() . '</td>';
    }
}
