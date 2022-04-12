<?php

namespace PayPal\Braintree\Model\Paypal;

use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\Type\Config as Cache;
use PayPal\Braintree\Gateway\Config\PayPalCredit\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\AuthenticationException;
use Zend_Http_Client;

class CreditApi
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Config
     */
    private $config;

    /**
     * CreditApi constructor.
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     * @param Cache $cacheManager
     * @param Config $config
     */
    public function __construct(
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        Cache $cacheManager,
        Config $config
    ) {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cacheManager;
        $this->config = $config;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getAuthorization(): string
    {
        if ($this->accessToken) {
            return $this->getAuthorizationToken();
        }

        $cacheKey = 'braintree_credit_api_token';
        if ($this->cache->test($cacheKey)) {
            $this->accessToken = $this->cache->load($cacheKey);
            return $this->getAuthorizationToken();
        }

        $clientId = $this->config->getClientId();
        $secret = $this->config->getSecret();

        try {
            $response = $this->request(
                $this->getAuthorizationUrl(),
                'grant_type=client_credentials',
                ['Accept: application/json'],
                ['userpwd' => $clientId . ':' . $secret]
            );

            if (!isset($response->token_type, $response->access_token)) {
                throw new AuthenticationException(__('No token returned'));
            }

            $this->accessToken = $response->token_type . ' ' . $response->access_token;
            $this->cache->save($this->accessToken, $cacheKey, [], $response->expires_in ?: 10000);
        } catch (LocalizedException $e) {
            throw new AuthenticationException(__('Unable to retrieve PayPal API token. ' . $e->getMessage()));
        }

        return $this->getAuthorizationToken();
    }

    /**
     * @param $price
     * @return array
     * @throws LocalizedException
     */
    public function getPriceOptions($price): array
    {
        $body = [
            'financing_country_code' => 'GB',
            'transaction_amount' => [
                'value' => $price,
                'currency_code' => 'GBP'
            ]
        ];

        $body = json_encode($body);

        $response = $this->request(
            $this->getCalcUrl(),
            $body,
            [
                'Accept: application/json',
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body),
                'Authorization: ' . $this->getAuthorization()
            ]
        );

        if (empty($response->financing_options)) {
            throw new LocalizedException(__('No financing options available from API'));
        }

        $options = [];
        foreach ($response->financing_options as $option) {
            $qualifyingOptions = $option->qualifying_financing_options;
            if (empty($qualifyingOptions)) {
                throw new LocalizedException(__('No qualifying financing options available'));
            }

            foreach ($qualifyingOptions as $qualifyingOption) {
                if ($qualifyingOption->credit_financing->credit_type === 'INST' &&
                    $qualifyingOption->credit_financing->enabled === true
                ) {
                    $options[] = [
                        'term' => $qualifyingOption->credit_financing->term,
                        'monthly_payment' => $qualifyingOption->monthly_payment->value,
                        'instalment_rate' => $qualifyingOption->credit_financing->apr,
                        'cost_of_purchase' => $qualifyingOption->total_cost->value,
                        'total_inc_interest' =>
                            number_format(
                                $qualifyingOption->total_cost->value + $qualifyingOption->total_interest->value,
                                2
                            )
                    ];
                }
            }
        }

        return $options;
    }

    /**
     * @return string
     */
    private function getAuthorizationToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    private function getAuthorizationUrl(): string
    {
        $sandbox = $this->config->getSandbox() ? '.sandbox' : '';
        return sprintf('https://api%s.paypal.com/v1/oauth2/token', $sandbox);
    }

    /**
     * @return string
     */
    private function getCalcUrl(): string
    {
        $sandbox = $this->config->getSandbox() ? '.sandbox' : '';
        return sprintf('https://api%s.paypal.com/v1/credit/calculated-financing-options', $sandbox);
    }

    /**
     * @param $url
     * @param $body
     * @param array $headers
     * @param array $configuration
     * @return mixed|string
     * @throws LocalizedException
     */
    private function request($url, $body, $headers = [], $configuration = [])
    {
        $configuration['header'] = false;

        $this->curl->setConfig($configuration);
        $this->curl->write(
            Zend_Http_Client::POST,
            $url,
            '1.1',
            $headers,
            $body
        );

        $response = $this->curl->read();

        if (!$response) {
            throw new LocalizedException(__('No response from request to ' . $url));
        }

        $response = json_decode($response);

        if (!empty($response->error)) {
            throw new LocalizedException(__('Error returned with request to ' . $url . '. Error: ' . $response->error));
        }

        return $response;
    }
}
