<?php
declare(strict_types=1);

namespace Giorgijorji\LaravelTbcInstallment;

use Giorgijorji\LaravelTbcInstallment\Exceptions\InvalidProductException;
use Giorgijorji\LaravelTbcInstallment\Exceptions\InvalidProductPriceException;
use Giorgijorji\LaravelTbcInstallment\Exceptions\ProductsNotFoundException;
use Giorgijorji\LaravelTbcInstallment\Interfaces\LaravelTbcInstallmentInterface;
use Giorgijorji\LaravelTbcInstallment\Models\ProductModel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class LaravelTbcInstallment
 */
class LaravelTbcInstallment implements LaravelTbcInstallmentInterface
{
    /**
     * @var string
     * Test service url: https://test-api.tbcbank.ge
     * Production service url: https://api.tbcbank.ge
     * Base Path: /v1
     */
    protected $baseUri;

    /**
     * @var string
     * /oauth/token
     */
    protected $oAuthEndPoint;

    /** @var string */
    protected $applicationsEndpoint;

    /** @var string */
    protected $applicationConfirmEndpoint;

    /** @var string */
    protected $applicationCancelEndpoint;

    /** @var null */
    protected $oAuthAccessToken;

    /** @var null */
    protected $issuedAt;

    /** @var null */
    protected $expiresIn;

    /** @var null */
    protected $redirectUri;

    /** @var null */
    protected $sessionId;

    /** @var \Giorgijorji\LaravelTbcInstallment\Models\ProductModel */
    protected $productModel;

    /**
     * LaravelTbcInstallment constructor.
     */
    public function __construct()
    {
        $this->baseUri = (config('tbc-installment.environment') === 'production') ? 'https://api.tbcbank.ge' : 'https://test-api.tbcbank.ge';
        $this->oAuthEndPoint = '/oauth/token';
        $this->applicationsEndpoint = '/v1/online-installments/applications';
        $this->applicationConfirmEndpoint = '/v1/online-installments/applications/{session-id}/confirm';
        $this->applicationCancelEndpoint = '/v1/online-installments/applications/{session-id}/cancel';
        $this->oAuthAccessToken = null;
        $this->issuedAt = null;
        $this->expiresIn = null;
        $this->redirectUri = null;
        $this->sessionId = null;
        $this->productModel = new ProductModel();
    }

    /**
     * Process oAuth and get Bearer token
     * @return  void
     */
    private function processOauth(): void
    {
        $client = new Client([
            'base_uri' => $this->baseUri,
        ]);

        try {
            $response = $client->request('POST', $this->oAuthEndPoint, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'client_id' => config('tbc-installment.apiKey'),
                    'client_secret' => config('tbc-installment.apiSecret'),
                    'merchant-key' => config('tbc-installment.merchantKey'),
                    'grant_type' => 'client_credentials',
                    'scope' => 'online_installments',
                ],
            ]);
            $data = json_decode($response->getBody()->getContents());
            $this->oAuthAccessToken = $data->access_token ?? null;
            $this->issuedAt = $data->issued_at ?? null;
            $this->expiresIn = $data->expires_in ?? null;
            session()->put('tbc-installment.oAuthAccessToken', $this->oAuthAccessToken);
            session()->put('tbc-installment.issuedAt', $this->issuedAt);
            session()->put('tbc-installment.expiresIn', $this->expiresIn);
        } catch (GuzzleException $e) {
            report($e);
        }
    }

    /**
     * get oAuth token
     * @return string
     */
    private function getOauthToken(): string
    {
        if (empty(session()->get('tbc-installment'))) {
            $this->processOauth();
        } else {
            $tokenExpiresAt = (int) session()->get('tbc-installment.issuedAt') + (int) session()->get('tbc-installment.expiresIn');

            if ($tokenExpiresAt <= (time() - 1000)) {
                $this->processOauth();
            }
        }
        return session()->get('tbc-installment.oAuthAccessToken');
    }

    /**
     * Add single product array
     * @param array $product
     * @throws \Giorgijorji\LaravelTbcInstallment\Exceptions\InvalidProductException
     */
    public function addProduct(array $product): void
    {
        $result = $this->productModel->pushProduct($product);

        if (!$result) {
            throw new InvalidProductException();
        }
    }

    /**
     * Add multiple products
     * @param array $products
     * @throws \Giorgijorji\LaravelTbcInstallment\Exceptions\InvalidProductException
     */
    public function addProducts(array $products): void
    {
        $result = $this->productModel->pushProducts($products);

        if (!$result) {
            throw new InvalidProductException();
        }
    }

    /**
     * Get redirect url to tbc installment
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * Get session Id after successful application confirm
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @param string $invoiceId
     * @param float $priceTotal
     * @return array
     * @throws \Giorgijorji\LaravelTbcInstallment\Exceptions\ProductsNotFoundException
     * @throws \Giorgijorji\LaravelTbcInstallment\Exceptions\InvalidProductPriceException
     */
    public function applyInstallmentApplication(string $invoiceId, float $priceTotal): array
    {
        if ($this->productModel->getProducts()->count() === 0) {
            throw new ProductsNotFoundException();
        }

        if (!$this->productModel->validateTotalPrice($priceTotal)) {
            throw new InvalidProductPriceException();
        }

        $client = new Client([
            'base_uri' => $this->baseUri,
        ]);

        try {
            $response = $client->request('POST', $this->applicationsEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getOauthToken(),
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'merchantKey' => (string) config('tbc-installment.merchantKey'),
                    'priceTotal' => $priceTotal,
                    'campaignId' => (string) config('tbc-installment.campaignId'),
                    'invoiceId' => $invoiceId,
                    'products' => $this->productModel->getProducts()->toArray(),
                ],
            ]);

            $this->redirectUri = $response->getHeaders()['Location'][0];
            $this->sessionId = json_decode($response->getBody()->getContents())->sessionId;
            return $this->transformTbcMessage($response->getBody()->getContents());
        } catch (GuzzleException $e) {
            return $this->transformTbcMessage($e->getResponse()->getBody()->getContents());
        }
    }

    /**
     * Confirm tbc installment  Application
     * @param string $invoiceId
     * @param string $sessionId
     * @param float $priceTotal
     * @return array
     */
    public function confirm(string $invoiceId, string $sessionId, float $priceTotal): array
    {
        $client = new Client([
            'base_uri' => $this->baseUri,
        ]);

        $url = str_replace('{session-id}', $sessionId, $this->applicationConfirmEndpoint);
        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getOauthToken(),
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'merchantKey' => (string) config('tbc-installment.merchantKey'),
                    'priceTotal' => $priceTotal,
                    'campaignId' => (string) config('tbc-installment.campaignId'),
                    'invoiceId' => $invoiceId,
                ],
            ]);
            return $this->transformTbcMessage($response->getBody()->getContents());
        } catch (GuzzleException $e) {
            return $this->transformTbcMessage($e->getResponse()->getBody()->getContents());
        }
    }

    /**
     * @param string $sessionId
     * @return array
     */
    public function cancel(string $sessionId): array
    {
        $client = new Client([
            'base_uri' => $this->baseUri,
        ]);

        $url = str_replace('{session-id}', $sessionId, $this->applicationCancelEndpoint);
        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getOauthToken(),
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'merchantKey' => (string) config('tbc-installment.merchantKey'),
                ],
            ]);
            return $this->transformTbcMessage($response->getBody()->getContents());
        } catch (GuzzleException $e) {
            return $this->transformTbcMessage($e->getResponse()->getBody()->getContents());
        }
    }

    /**
     * Get All Products added
     * @return array
     */
    public function getProducts(): array
    {
        return $this->productModel->getProducts()->toArray();
    }

    protected function transformTbcMessage(string $message)
    {
        $message = json_decode($message);
        if (isset($message->status)) {
            $message = [
                'status_code' => $message->status,
                'message' => $message->detail,
            ];
        } else {
            $message = [
                'status_code' => 200,
                'message' => 'ok',
            ];
        }


        return $message;
    }
}
