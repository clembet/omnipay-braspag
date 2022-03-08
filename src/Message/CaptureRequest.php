<?php namespace Omnipay\Braspag\Message;


class CaptureRequest extends AbstractRequest
{
    protected $resource = 'sales';
    protected $requestMethod = 'PUT';


    public function getData()
    {
        $this->validate('transactionId', 'amount');
        //$data = parent::getData();
        $data = [];

        return $data;
    }

    public function sendData($data)
    {
        $this->validate('transactionId', 'amount');

        $url = $this->getEndpoint();

        $headers = [
            'MerchantId' => $this->getMerchantId(),
            'MerchantKey' => $this->getMerchantKey(),
            'Content-Type' => 'application/json',
        ];

        $url = sprintf(
            "%s/%s/capture?amount=%d",
            $this->getEndpoint(),
            $this->getTransactionID(),
            $this->getAmountInteger()
        );

        //print_r([$this->getMethod(), $url, $headers]);exit();
        $httpResponse = $this->httpClient->request($this->getMethod(), $url, $headers);
        $json = $httpResponse->getBody()->getContents();
        return $this->createResponse(@json_decode($json, true));
    }
}
