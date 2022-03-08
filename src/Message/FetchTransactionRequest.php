<?php namespace Omnipay\Braspag\Message;

class FetchTransactionRequest extends AbstractRequest
{
    protected $liveEndpoint = 'https://apiquery.braspag.com.br';
    protected $testEndpoint = 'https://apiquerysandbox.braspag.com.br';
    protected $resource = 'sales';
    protected $requestMethod = 'GET';

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        return parent::getData();
    }

    public function sendData($data)
    {
        $this->validate('transactionId');

        $url = $this->getEndpoint();

        $headers = [
            'MerchantId' => $this->getMerchantId(),
            'MerchantKey' => $this->getMerchantKey(),
            'Content-Type' => 'application/json',
        ];

        $url = sprintf(
            '%s/%s',
            $this->getEndpoint(),
            $this->getTransactionID()
        );

        $httpResponse = $this->httpClient->request($this->getMethod(), $url, $headers);
        $json = $httpResponse->getBody()->getContents();
        return $this->createResponse(@json_decode($json, true));
    }

    protected function getEndpoint()
    {
        $version = $this->getVersion();
        $endPoint = ($this->getTestMode()?$this->testEndpoint:$this->liveEndpoint);
        return  "{$endPoint}/v{$version}/{$this->getResource()}";
    }

}
