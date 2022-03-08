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

        $data = [];
        switch(strtolower($this->getPaymentType()))
        {
            case 'creditcard':
                $data = $this->getDataCreditCard();
                break;

            case 'boleto':
                $data = $this->getDataBoleto();
                break;

            case 'pix':
                $data = $this->getDataPix();
                break;

            default:
                $data = $this->getDataCreditCard();
        }

        return $data;
    }


}
