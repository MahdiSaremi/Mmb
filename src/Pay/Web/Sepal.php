<?php
#auto-name
namespace Mmb\Pay\Web;
use Mmb\Pay\PayDriver;
use Mmb\Pay\PayInfo;

class Sepal extends PayDriver
{

    public function requestPay($amount, $options, &$saved_data, &$result_id)
    {
        $key = $this->key;
		if($this->debug) $key = 'test';

		$params = array(
			'apiKey' => $key,
			'amount' => $amount * 10,
			'callbackUrl' => $this->callbackUrl,
			'invoiceNumber' => rand(1, 100000),
			'payerName' => $options['name'] ?? '',
			'payerMobile' => $options['phone'] ?? null,
			'payerEmail' => $options['email'] ?? null,
			'description' => $options['description'] ?? null,
		);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://sepal.ir/api/request.json');
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/json'));
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		if($response && isset($response->status))
        {
            if($response->status == 1)
            {
                $paymentUrl = 'https://sepal.ir/payment/'.$response->paymentNumber;
                $result_id = $response->paymentNumber;

                return $paymentUrl;
            }
            else
            {
                $this->error('' . $response->message);
            }
		}
		else
        {
            $this->error(400);
		}
    }

    public function optionsList()
    {
        return [
            'name',
            'phone',
            'email',
            'description',
        ];
    }

    public function getCurrent()
    {
        return $_POST['paymentNumber'] ?? false;
    }

    public function verify(PayInfo $info)
    {
		$key = $this->key;
		if($this->debug) $key = 'test';
		
		$params = array(
			'apiKey' => $key,
			'paymentNumber' => $_POST['paymentNumber'],
		);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://sepal.ir/api/verify.json');
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/json'));
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode( $params ));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		if(isset($response->status) && $response->status == 1)
        {
            $info->ref = 0;
			return true;
		}
		else
        {
			return false;
		}
        
    }
    
}
