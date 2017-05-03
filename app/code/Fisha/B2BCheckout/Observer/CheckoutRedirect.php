<?php

namespace Fisha\B2BCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckoutRedirect implements ObserverInterface
{

    protected $_responseFactory;
    protected $_url;
    protected $_scopeConfig;
    protected $_scopeResolver;

    public function __construct(
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver
    ) {
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        $this->_scopeConfig = $scopeConfig;
        $this->_scopeResolver = $scopeResolver;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_scopeConfig->isSetFlag('b2bcheckout/checkout/disable_checkout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_scopeResolver->getScope()))
        {
            $this->_responseFactory->create()->setRedirect($this->_url->getUrl('checkout/cart/index'))->sendResponse();
            exit();
        }
    }

}
