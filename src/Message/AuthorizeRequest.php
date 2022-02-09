<?php namespace Omnipay\Braspag\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\ItemBag;

class AuthorizeRequest extends AbstractRequest
{
    protected $resource = 'sales';
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */

    public function getData()
    {
        $this->validate('customer', 'paymentProvider', 'paymentType');
        $card = $this->getCard();

        $data = [
            "MerchantOrderId"=>$this->getOrderId(),
            "Customer"=>$this->getCustomerData(),
            "Payment"=>[
              "Provider"=>$this->getTestMode()?"Simulado":$this->getPaymentProvider(), // https://braspag.github.io/manual/braspag-pagador#lista-de-providers
              "Type"=>$this->getPaymentType(),
              "Amount"=>$this->getAmount(),
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

        return [
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
            "DeliveryAddress"=>[
                "Street"=>$card->getShippingAddress1(),
                "Number"=>$card->getShippingNumber(),
                "Complement"=>$card->getShippingAddress2(),
                "ZipCode"=>$card->getShippingPostcode(),
                "City"=>$card->getShippingCity(),
                "State"=>$card->getShippingState(),
                "Country"=>"BRA",
                "District"=>$card->getShippingDistrict()
            ]
        ];
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
