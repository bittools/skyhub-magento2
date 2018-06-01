<?php

namespace BitTools\SkyHub\Block\Adminhtml\Catalog\Product\Edit\Button;

use BitTools\SkyHub\Block\Widget\Button\GenericButton;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Backend\Block\Widget\Context as WidgetContext;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Integrate extends GenericButton implements ButtonProviderInterface
{

    /** @var ActionContext */
    protected $actionContext;

    public function __construct(WidgetContext $widgetContext, ActionContext $actionContext)
    {
        parent::__construct($widgetContext);
        $this->actionContext = $actionContext;
    }


    /**
     * Retrieve button-specified settings
     *
     * @return array|bool
     */
    public function getButtonData()
    {
        if (!$this->getProductId()) {
            return false;
        }

        return [
            'label'      => __('Send to SkyHub'),
            'class'      => 'action-secondary',
            'on_click'   => sprintf("location.href = '%s';", $this->getIntegrateUrl($this->getProductId())),
            'sort_order' => 20,
        ];
    }


    /**
     * Get integrate URL
     *
     * @param integer $productId
     *
     * @return string
     */
    public function getIntegrateUrl($productId)
    {
        return $this->getUrl('bittools_skyhub/integrate_catalog/product', ['id' => $productId]);
    }


    /**
     * @return integer|null
     */
    protected function getProductId()
    {
        $productId = $this->getRequest()->getParam('id', null);
        return $productId;
    }


    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    protected function getRequest()
    {
        return $this->actionContext->getRequest();
    }
}
