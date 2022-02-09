<?php namespace Omnipay\Braspag\Message;


abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $liveEndpoint = 'https://api.braspag.com.br';
    protected $liveEndpointConsultas = 'https://apiquery.braspag.com.br';
    protected $testEndpoint = 'https://apisandbox.braspag.com.br';
    protected $testEndpointConsultas = 'https://apiquerysandbox.braspag.com.br';
    protected $version = 2;
    protected $requestMethod = 'POST';
    protected $resource = '';

    public function sendData($data)
    {
        $method = $this->requestMethod;
        $url = $this->getEndpoint();

        $headers = [
            'MerchantId' => $this->getMerchantId(),
            'MerchantKey' => $this->getMerchantKey(),
            'Content-Type' => 'application/json',
        ];

        //print_r([$method, $url, $headers, json_encode($data)]);exit();
        $response = $this->httpClient->request(
            $method,
            $url,
            $headers,
            $this->toJSON($data)
            //http_build_query($data, '', '&')
        );
        //print_r($response);
        //print_r($data);

        if ($response->getStatusCode() != 200 && $response->getStatusCode() != 201 && $response->getStatusCode() != 400) {
            $array = [
                'error' => [
                    'code' => $response->getStatusCode(),
                    'message' => $response->getReasonPhrase()
                ]
            ];

            return $this->response = $this->createResponse($array);
        }

        $json = $response->getBody()->getContents();
        $array = @json_decode($json, true);
        print_r($array);

        return $this->response = $this->createResponse(@$array);
    }

    protected function setBaseEndpoint($value)
    {
        $this->baseEndpoint = $value;
    }

    public function __get($name)
    {
        return $this->getParameter($name);
    }

    protected function setRequestMethod($value)
    {
        return $this->requestMethod = $value;
    }

    protected function decode($data)
    {
        return json_decode($data, true);
    }

    public function getEmail()
    {
        return $this->getParameter('email');
    }

    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getMerchantKey()
    {
        return $this->getParameter('merchantKey');
    }

    public function setMerchantKey($value)
    {
        return $this->setParameter('merchantKey', $value);
    }

    public function setOrderId($value)
    {
        return $this->setParameter('order_id', $value);
    }
    public function getOrderId()
    {
        return $this->getParameter('order_id');
    }

    public function setInstallments($value)
    {
        return $this->setParameter('installments', $value);
    }
    public function getInstallments()
    {
        return $this->getParameter('installments');
    }

    public function setSoftDescriptor($value)
    {
        return $this->setParameter('soft_descriptor', $value);
    }
    public function getSoftDescriptor()
    {
        return $this->getParameter('soft_descriptor');
    }

    public function getPaymentProvider()
    {
        return $this->getParameter('paymentProvider');
    }

    public function setPaymentProvider($value)
    {
        $this->setParameter('paymentProvider', $value);
    }

    public function getPaymentType()
    {
        return $this->getParameter('paymentType');
    }

    public function setPaymentType($value)
    {
        $this->setParameter('paymentType', $value);
    }

    public function getAmount()
    {
        return (int)round((parent::getAmount()*100.0), 0);
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getMethod()
    {
        return $this->requestMethod;
    }

    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }

    protected function getEndpoint()
    {
        $version = $this->getVersion();
        $endPoint = ($this->getTestMode()?$this->testEndpoint:$this->liveEndpoint);
        return  "{$endPoint}/v{$version}/{$this->getResource()}";
    }

    public function getData()
    {
        $this->validate('merchantId', 'merchantKey');

        return [
        ];
    }

    public function toJSON($data, $options = 0)
    {
        if (version_compare(phpversion(), '5.4.0', '>=') === true) {
            return json_encode($data, $options | 64);
        }
        return str_replace('\\/', '/', json_encode($data, $options));
    }
}
