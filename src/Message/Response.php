<?php namespace Omnipay\Braspag\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Pagarme Response
 *
 * This is the response class for all Pagarme requests.
 *
 * @see \Omnipay\Pagarme\Gateway
 */
class Response extends AbstractResponse
{
    /**
     * Is the transaction successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        //$result = $this->data;
        if(isset($this->data['error']) || isset($this->data['error_messages']))
            return false;

        if (isset($this->data['Payment']['Status']) && isset($this->data['Payment']['ReasonCode']))
            if($this->data['Payment']['ReasonCode']==0)
                return true;

        return false;
    }

    /**
     * Get the transaction reference.
     *
     * @return string|null
     */
    public function getTransactionReference()
    {
        if(isset($this->data['Payment']['PaymentId']))
            return @$this->data['Payment']['PaymentId'];

        return NULL;
    }

    public function getTransactionAuthorizationCode()
    {
        if(isset($this->data['Payment']['PaymentId']))
            return @$this->data['Payment']['PaymentId'];

        return NULL;
    }

    public function getStatus() // https://braspag.github.io/manual/braspag-pagador#lista-de-status-da-transa%C3%A7%C3%A3o
    {
        $status = null;
        if(isset($this->data['Payment']['Status']))
            $status = @$this->data['Payment']['Status'];
        else
        {
            if(isset($this->data['Status']))
                $status = @$this->data['Status'];
        }

        return $status;
    }

    public function isPaid()
    {
        $status = $this->getStatus();
        return $status==2;
    }

    public function isAuthorized()
    {
        $status = $this->getStatus();
        return $status==1;
    }

    public function isPending()
    {
        $status = $this->getStatus();
        return $status==12;
    }

    public function isVoided()
    {
        $status = $this->getStatus();
        return ($status==10||$status==11);
    }

    /**
     * Get the error message from the response.
     *
     * Returns null if the request was successful.
     *
     * @return string|null
     */
    public function getMessage()
    {
        //print_r($this->data);
        if(isset($this->data['error']))
            return "{$this->data['error']['code']} - {$this->data['error']['message']}";

        if(isset($this->data['Payment']['ReasonMessage']))
            return @$this->data['Payment']['ReasonCode']." - ".@$this->data['Payment']['ReasonMessage'];

        if(isset($this->data['ReasonMessage']))
            return @$this->data['ReasonCode']." - ".@$this->data['ReasonMessage'];

        return null;
    }

    public function getBoleto()
    {
        $data = $this->getData();
        $boleto = array();
        $boleto['boleto_url'] = @$data['Payment']['Url'];
        $boleto['boleto_url_pdf'] = @$data['Payment']['Url'];
        $boleto['boleto_barcode'] = @$data['Payment']['DigitableLine'];
        $boleto['boleto_expiration_date'] = @$data['Payment']['ExpirationDate'];
        $boleto['boleto_valor'] = (@$data['Payment']['Amount']*1.0)/100.0;
        $boleto['boleto_transaction_id'] = @$data['Payment']['PaymentId'];
        //@$this->setTransactionReference(@$data['transaction_id']);

        return $boleto;
    }

    public function getPix()
    {
        $data = $this->getData();
        $boleto = array();
        $boleto['pix_qrcodebase64image'] = @$data['Payment']['QrcodeBase64Image'];
        $boleto['pix_qrcodestring'] = @$data['Payment']['QrCodeString'];
        $boleto['pix_valor'] = (@$data['Payment']['Amount']*1.0)/100.0;
        $boleto['pix_transaction_id'] = @$data['Payment']['PaymentId'];
        //@$this->setTransactionReference(@$data['transaction_id']);

        return $boleto;
    }
}