<?php
declare(strict_types=1);

namespace Giorgijorji\LaravelTbcInstallment;

use Giorgijorji\LaravelTbcInstallment\Interfaces\LaravelTbcInstallmentInterface;
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

    /** @var array */
    protected $products;

    /** @var null */
    protected $redirectUri;

    /** @var null */
    protected $sessionId;

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
        $this->products = [];
        $this->redirectUri = null;
        $this->sessionId = null;
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
     * @return string
     */
    private function getOauthToken(): string
    {
        if (empty(session()->get('tbc-installment'))) {
            $this->processOauth();
            return session()->get('tbc-installment.oAuthAccessToken');
        } else {
            $tokenExpiresAt = (int) session()->get('tbc-installment.issuedAt') + (int) session()->get('tbc-installment.expiresIn');

            if ($tokenExpiresAt <= (time() - 1000)) {
                $this->processOauth();
                return session()->get('tbc-installment.oAuthAccessToken');
            }
        }
        return session()->get('tbc-installment.oAuthAccessToken');
    }

    /**
     * @param array $product
     */
    public function addProduct(array $product): void
    {
        $this->products[] = $product;
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
     * @return  void
     */
    public function applyInstallmentApplication(string $invoiceId, float $priceTotal): void
    {
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
                    'products' => $this->products,
                ],
            ]);

            $this->redirectUri = $response->getHeaders()['Location'][0];
            $this->sessionId = json_decode($response->getBody()->getContents())->sessionId;
        } catch (GuzzleException $e) {
            // Report Exception
            report($e);
        }
    }

    /**
     * Confirm tbc installment  Application
     * @param string $invoiceId
     * @param float $priceTotal
     * @return  void
     */
    public function confirm(string $invoiceId, float $priceTotal): void
    {
        $client = new Client([
            'base_uri' => $this->baseUri,
        ]);

        $url = str_replace('{session-id}', $this->getSessionId(), $this->applicationConfirmEndpoint);
        try {
            $client->request('POST', $url, [
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
        } catch (GuzzleException $e) {
            // Report Exception
            report($e);
        }
    }

    /**
     * @param string $sessionId
     * @return void
     */
    public function cancel(string $sessionId): void
    {
        $client = new Client([
            'base_uri' => $this->baseUri,
        ]);

        $url = str_replace('{session-id}', $sessionId, $this->applicationCancelEndpoint);
        try {
            $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getOauthToken(),
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'merchantKey' => (string) config('tbc-installment.merchantKey'),
                ],
            ]);
        } catch (GuzzleException $e) {
            // Report Exception
            report($e);
        }
    }
}
