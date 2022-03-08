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
        //print_r($array);

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

    public function getDueDate()
    {
        $dueDate = $this->getParameter('dueDate');
        if($dueDate)
            return $dueDate;

        $time = localtime(time());
        $ano = $time[5]+1900;
        $mes = $time[4]+1+1;
        $dia = 1;// $time[3];
        if($mes>12)
        {
            $mes=1;
            ++$ano;
        }

        $dueDate = sprintf("%04d-%02d-%02d", $ano, $mes, $dia);
        $this->setDueDate($dueDate);

        return $dueDate;
    }

    public function getTransactionID()
    {
        return $this->getParameter('transactionId');
    }

    public function setTransactionID($value)
    {
        return $this->setParameter('transactionId', $value);
    }

    public function setDueDate($value)
    {
        return $this->setParameter('dueDate', $value);
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

    public function getDataCreditCard()
    {
        $this->validate('card');
        $card = $this->getCard();

        $data = [
            "MerchantOrderId"=>$this->getOrderId(),
            "Customer"=>$this->getCustomerData(),
            "Payment"=>[
                "Provider"=>$this->getTestMode()?"Simulado":$this->getPaymentProvider(), // https://braspag.github.io/manual/braspag-pagador#lista-de-providers
                "Type"=>"CreditCard",
                "Amount"=>$this->getAmountInteger(),
                "Currency"=>"BRL",
                "Country"=>"BRA",
                "Installments"=>$this->getInstallments(),
                "Interest"=>"ByMerchant",
                "Capture"=>false, // true faz a captura, e false é apenas autorização sem lançar na fatura precisando capturar depois
                "Authenticate"=>false,
                "Recurrent"=>false,
                "SoftDescriptor"=>$this->getSoftDescriptor(),
                "DoSplit"=>false,
                "CreditCard"=>[
                    "CardNumber"=>$card->getNumber(),
                    "Holder"=>$card->getName(),
                    "ExpirationDate"=>sprintf("%02d/%04d", $card->getExpiryMonth(), $card->getExpiryYear()),
                    "SecurityCode"=>$card->getCvv(),
                    "Brand"=>$card->getBrand(),
                    "SaveCard"=>"false",
                    "Alias"=>"",
                    /*"CardOnFile"=>[
                       "Usage"=>"Used",
                       "Reason"=>"Unscheduled"
                    ]*/
                ],
                /*"Credentials"=>[
                   "Code"=>"9999999",
                   "Key"=>"D8888888",
                   "Password"=>"LOJA9999999",
                   "Username"=>"#Braspag2018@NOMEDALOJA#",
                   "Signature"=>"001"
                ],
                "ExtraDataCollection"=>[
                      [
                      "Name"=>"NomeDoCampo",
                      "Value"=>"ValorDoCampo"
                      ]
                  ]*/
            ]
        ];

        return $data;
    }

    public function getDataBoleto()
    {
        $customer = $this->getCustomerData();
        unset($customer["DeliveryAddress"]);

        $data = [
            "MerchantOrderId"=>$this->getOrderId(),
            "Customer"=>$customer,
            "Payment"=>[
                "Provider"=>$this->getTestMode()?"Simulado":$this->getPaymentProvider(), // https://braspag.github.io/manual/braspag-pagador#lista-de-providers
                "Type"=>"Boleto",
                "Amount"=>$this->getAmountInteger(),
                //"BoletoNumber"=>$this->getOrderId(),
                //"Assignor"=> $this->getSoftDescriptor(),
                //"Demonstrative"=> "Compra em ".$this->getSoftDescriptor(),
                "ExpirationDate"=> $this->getDueDate(),
                //"Identification"=> "CNPJ do cedente",
                //"Instructions"=> "Aceitar somente até a data de vencimento.",
                //"DaysToFine"=> 1,  // só para bradesco
                //"FineRate"=> 10.00000,// só para bradesco
                //"FineAmount"=> 1000,// só para bradesco
                //"DaysToInterest"=> 1,// só para bradesco
                //"InterestRate"=> 0.00000,// só para bradesco
                //"InterestAmount"=> 0,// só para bradesco
                //"DiscountAmount"=> 0,// só para bradesco
                //"DiscountLimitDate"=> "2017-12-31",// só para bradesco
                //"DiscountRate"=> 0.00000// só para bradesco
            ]
        ];

        return $data;
    }

    public function getDataPix()
    {
        $customer = $this->getCustomerData();
        unset($customer["Address"]);
        unset($customer["DeliveryAddress"]);

        $data = [
            "MerchantOrderId"=>$this->getOrderId(),
            "Customer"=>$customer,
            "Payment"=>[
                "Provider"=>$this->getPaymentProvider(), // https://braspag.github.io/manual/braspag-pagador#lista-de-providers
                "Type"=>"Pix",
                "Amount"=>$this->getAmountInteger(),
            ]
        ];

        return $data;
    }

    public function getCustomer()
    {
        return $this->getParameter('customer');
    }

    public function setCustomer($value)
    {
        return $this->setParameter('customer', $value);
    }

    public function getCustomerData()
    {
        $card = $this->getCard();
        $customer = $this->getCustomer();

        $data = [
            "Name"=>$customer->getName(),
            "Identity"=>$customer->getDocumentNumber(),
            "IdentityType"=>"CPF",
            "Email"=>$customer->getEmail(),
            "Birthdate"=>$customer->getBirthday('Y-m-d'),// formato ISO
            "IpAddress"=>$this->getClientIp(),
            "Address"=>[
                "Street"=>$customer->getBillingAddress1(),
                "Number"=>$customer->getBillingNumber(),
                "Complement"=>$customer->getBillingAddress2(),
                "ZipCode"=>$customer->getBillingPostcode(),
                "City"=>$customer->getBillingCity(),
                "State"=>$customer->getBillingState(),
                "Country"=>"BRA",
                "District"=>$customer->getBillingDistrict()
            ],
        ];

        if(strcmp(strtolower($this->getPaymentType()), "creditcard")==0)
        {
            $data["DeliveryAddress"]=[
                "Street"=>$card->getShippingAddress1(),
                "Number"=>$card->getShippingNumber(),
                "Complement"=>$card->getShippingAddress2(),
                "ZipCode"=>$card->getShippingPostcode(),
                "City"=>$card->getShippingCity(),
                "State"=>$card->getShippingState(),
                "Country"=>"BRA",
                "District"=>$card->getShippingDistrict()
            ];
        }

        return $data;
    }

    public function getItemData()
    {
        $data = [];
        $items = $this->getItems();

        if ($items) {
            foreach ($items as $n => $item) {
                $item_array = [];
                $item_array['id'] = $n+1;
                $item_array['title'] = $item->getName();
                $item_array['description'] = $item->getName();
                //$item_array['category_id'] = $item->getCategoryId();
                $item_array['quantity'] = (int)$item->getQuantity();
                //$item_array['currency_id'] = $this->getCurrency();
                $item_array['unit_price'] = (double)($this->formatCurrency($item->getPrice()));

                array_push($data, $item_array);
            }
        }

        return $data;
    }
}
