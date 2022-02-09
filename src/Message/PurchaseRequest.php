<?php namespace Omnipay\Braspag\Message;

class PurchaseRequest extends AuthorizeRequest
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
        // faz o registro do cliente, se não houver especificado

        $data = parent::getData();
        if(strcmp(strtolower($this->getPaymentType()), "creditcard")==0)
            $data["Payment"]["Capture"] = true;
        //$this->getNotifyUrl()  // verificar se no painel é especificado uma url para notificação

        return $data;
    }
}
