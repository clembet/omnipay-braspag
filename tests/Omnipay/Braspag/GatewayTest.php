<?php namespace Omnipay\Braspag;

use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testAuthorize()
    {
        $request = $this->gateway->authorize(array('amount' => '10.00'));

        $this->assertInstanceOf(\Omnipay\Braspag\Message\AuthorizeRequest::class, $request);
        $this->assertSame(1000, $request->getAmountInteger());
    }
}
