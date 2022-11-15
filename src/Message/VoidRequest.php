<?php namespace Omnipay\Braspag\Message;

/**
 *  O Cancelamento é aplicavel a transações do mesmo dia sendo autorizadas ou aprovadas
 *  O Estono é aplicável para transações onde virou o dia, seguindo o processo do adquirente
 * <code>
 *   // Do a refund transaction on the gateway
 *   $transaction = $gateway->void(array(
 *       'transactionId'     => $transactionCode,
 *   ));
 *
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *   }
 * </code>
 */

class VoidRequest extends AbstractRequest   // está dando  erro para vendas com cartao parcelado, não permitindo estornar individualmente o pagamento
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
            "%s/%s/void?amount=%d",
            $this->getEndpoint(),
            $this->getTransactionID(),
            (int)($this->getAmount()*100.0)
        );

        //print_r([$this->getMethod(), $url, $headers]);exit();
        $httpResponse = $this->httpClient->request($this->getMethod(), $url, $headers);
        $json = $httpResponse->getBody()->getContents();
        return $this->createResponse(@json_decode($json, true));
    }
}
