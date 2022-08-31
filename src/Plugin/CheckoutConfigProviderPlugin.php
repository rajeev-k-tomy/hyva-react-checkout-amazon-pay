<?php
declare(strict_types=1);

namespace Hyva\ReactCheckoutAmazonPay\Plugin;

use Amazon\Pay\Helper\Data as AmazonPayHelper;
use Amazon\Pay\Model\AmazonConfig;
use Hyva\ReactCheckout\ViewModel\CheckoutConfigProvider;
use Magento\Framework\Serialize\SerializerInterface;

class CheckoutConfigProviderPlugin
{
    private SerializerInterface $serializer;
    private AmazonConfig $amazonConfig;
    private AmazonPayHelper $amazonHelper;

    public function __construct(
        SerializerInterface $serializer,
        AmazonConfig $amazonConfig,
        AmazonPayHelper $amazonHelper
    ) {
        $this->serializer = $serializer;
        $this->amazonHelper = $amazonHelper;
        $this->amazonConfig = $amazonConfig;
    }

    /**
     *  @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(CheckoutConfigProvider $subject, string $serializedResult): string
    {
        if (!$this->amazonConfig->isEnabled()) {
            return $serializedResult;
        }

        $result = $this->serializer->unserialize($serializedResult);

        $result['payment']['amazonPay'] = [
            'region'                    => $this->amazonConfig->getRegion(),
            'code'                      => \Amazon\Pay\Gateway\Config\Config::CODE,
            'is_method_available'       => $this->amazonConfig->isPayButtonAvailableAsPaymentMethod(),
            'is_pay_only'               => $this->amazonHelper->isPayOnly(),
            'is_lwa_enabled'            => $this->amazonConfig->isLwaEnabled(),
            'is_guest_checkout_enabled' => $this->amazonConfig->isGuestCheckoutEnabled(),
            'has_restricted_products'   => $this->amazonHelper->hasRestrictedProducts(),
            'is_multicurrency_enabled'  => $this->amazonConfig->multiCurrencyEnabled()
        ];

        return $this->serializer->serialize($result);
    }
}
