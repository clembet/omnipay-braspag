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

    public function isVoided()
    {
        $status = $this->getStatus();
        return $status==10;
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
}