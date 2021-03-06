<?php

namespace Fisha\B2BCheckout\Controller\Order;

class Create extends \Magento\Framework\App\Action\Action
{

    protected $_chckoutSession;
    protected $_customerSession;
    protected $_urlBuilder;
    protected $_scopeConfig;
    protected $_quoteManagement;
    protected $_customerRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_urlBuilder = $urlBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_quoteManagement = $quoteManagement;
        $this->_customerRepository = $customerRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$shippingAddress = $this->_customerSession->getCustomer()->getDefaultShippingAddress()) {
            $this->messageManager->addWarning(__('Please %1 new default address.', $this->getAddressEditLink()));
            return $resultRedirect->setPath('checkout/cart');
        }
        $customer = $this->_customerRepository->getById($this->_customerSession->getCustomer()->getId());

        $quote = $this->getQuote();
        $quote->assignCustomer($customer);

        $shippingMethod = $this->_scopeConfig->getValue(
            'b2bcheckout/order/shipping_method',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
                        ->collectShippingRates()
                        ->setShippingMethod($shippingMethod);

        $paymentMethod = $this->_scopeConfig->getValue(
            'b2bcheckout/order/payment_method',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $quote->setPaymentMethod($paymentMethod);
        $quote->setInventoryProcessed(false);

        $quote->getPayment()->importData(['method' => $paymentMethod]);
        $quote->collectTotals()->save();

        $order = $this->_quoteManagement->submit($quote);

        $this->_checkoutSession
            ->setLastQuoteId($this->getQuote()->getId())
            ->setLastSuccessQuoteId($this->getQuote()->getId())
            ->clearHelperData();

        if ($order) {
            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();

            $this->_checkoutSession
                ->setLastOrderId($order->getId())
                ->setRedirectUrl($redirectUrl)
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastOrderStatus($order->getStatus());

            return $resultRedirect->setPath('checkout/onepage/success');
        }
        return $resultRedirect->setPath('checkout/cart');
    }

    public function getQuote()
    {
        return $this->getOnepage()->getQuote();
    }

    public function getOnepage()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Type\Onepage');
    }

    public function getAddressEditLink()
    {
        return sprintf('<a href="%s" title="%s">%s</a>',
            $this->_urlBuilder->getUrl('customer/address/new'),
            __('Create new address'),
            __('create')
        );
    }

}
