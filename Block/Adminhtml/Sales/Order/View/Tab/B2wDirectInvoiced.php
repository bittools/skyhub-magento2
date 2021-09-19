<?php

/**
 * BSeller Platform | B2W - Companhia Digital
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @category  BitTools
 * @package   BitTools_SkyHub
 *
 * @copyright Copyright (c) 2021 B2W Digital - BSeller Platform. (http://www.bseller.com.br)
 *
 * Access https://ajuda.skyhub.com.br/hc/pt-br/requests/new for questions and other requests.
 */

namespace BitTools\SkyHub\Block\Adminhtml\Sales\Order\View\Tab;

use BitTools\SkyHub\Model\Config\Source\Skyhub\Status\Type;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;

/**
 * B2wDirectInvoiced class
 */
class B2wDirectInvoiced extends AbstractOrder implements TabInterface
{
    /** @var string */
    protected $_template = 'order/view/tab/b2w_direct_invoiced.phtml';

    /**
     * @var string
     */
    protected $bsellerSkyhubJson;

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('SkyHub Order Xml Nfe');
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        if (!$this->getOrder()) {
            return false;
        }

        /** @var \BitTools\SkyHub\Api\Data\OrderInterface $info */
        $info = $this->getOrder()->getExtensionAttributes()->getSkyhubInfo();
        /*if ($info->getSkyhubNfeXml()) {
            return false;
        }*/

        $calculationType = '';
        if (isset($this->getIntegrationJson()['calculation_type'])) {
            $calculationType = $this->getIntegrationJson()['calculation_type'];
        }

        $approved = Type::TYPE_APPROVED;
        $bsellerSkyhubStatus = $info->getSkyhubStatus();
        return $calculationType == 'b2wentregadirect'
            && $bsellerSkyhubStatus == $approved;
    }

    /**
     * Get Integration Json
     *
     * @return array
     */
    public function getIntegrationJson()
    {
        if ($this->bsellerSkyhubJson) {
            return $this->bsellerSkyhubJson;
        }

        /** @var \BitTools\SkyHub\Api\Data\OrderInterface $info */
        $info = $this->getOrder()->getExtensionAttributes()->getSkyhubInfo();
        $jsonFormat = json_decode($info->getDataSource(), true);
        $this->bsellerSkyhubJson = $jsonFormat;
        return $this->bsellerSkyhubJson;
    }

    /**
     * Submit URL getter
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('bittools_skyhub/sales_order_invoicedxml/uploadxml');
    }
}