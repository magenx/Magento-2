<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Model;

use Magento\Framework\Locale\ResolverInterface;
use PayPal\Braintree\Gateway\Config\PayPal\Config;

class LocaleResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ResolverInterface $resolver
     * @param Config $config
     */
    public function __construct(ResolverInterface $resolver, Config $config)
    {
        $this->resolver = $resolver;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultLocalePath(): string
    {
        return $this->resolver->getDefaultLocalePath();
    }

    /**
     * @inheritdoc
     */
    public function setDefaultLocale($locale)
    {
        return $this->resolver->setDefaultLocale($locale);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultLocale(): string
    {
        return $this->resolver->getDefaultLocale();
    }

    /**
     * @inheritdoc
     */
    public function setLocale($locale = null)
    {
        return $this->resolver->setLocale($locale);
    }

    /**
     * Gets store's locale or the `en_US` locale if store's locale does not supported by PayPal.
     *
     * @return string
     */
    public function getLocale(): string
    {
        $locale = $this->resolver->getLocale();
        $allowedLocales = $this->config->getValue('supported_locales');

        if (!empty($allowedLocales)) {
            if (strpos($allowedLocales, $locale) !== false) {
                return $locale;
            }
        }

        return 'en_US';
    }

    /**
     * @inheritdoc
     */
    public function emulate($scopeId)
    {
        return $this->resolver->emulate($scopeId);
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        return $this->resolver->revert();
    }
}
