<?php
#auto-name
namespace Mmb\Pay\Web;
use Mmb\Pay\PayDriver;
use Mmb\Pay\PayInfo;

class IDPay extends PayDriver
{

	public function requestPay($amount, $options, &$saved_data, &$result_id)
	{
		$dataa = array(
			'order_id' => rand(1, 100000),
			'amount' => $amount * 10,
			'desc' => $options['description'] ?? null,
			'callback' => $this->callbackUrl,
		);
		if(isset($options['name']))     $dataa['name']  =  $options['name'];
		if(isset($options['phone']))    $dataa['phone'] =  $options['phone'];
		if(isset($options['mail']))     $dataa['mail']  =  $options['mail'];

		$headers = [
		  'Content-Type: application/json',
		  'X-API-KEY: ' . $this->key,
		];
		if($this->debug) $headers[] = 'X-SANDBOX: 1';

		$jsonData = json_encode($dataa);
		$ch = curl_init("https://api.idpay.ir/v1.1/payment");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		$err 	= curl_error($ch);
		curl_close($ch);

		$result = json_decode($result, true);

		if ($err)
        {
			$this->error(400);
		}
		else
        {
			if($result['error_code'] ?? false)
            {
                $this->error($result['error_code']);
            }
			else
            {
                $result_id = $result['id'];
				return $result['link'];
			}
		}
	}

    public function optionsList()
    {
        return [
            'description',
            'name',
            'phone',
            'mail',
        ];
    }

    public function getCurrent()
    {
        return $_GET['id'] ?? $_POST['id'] ?? false;
    }

    public function getCurrentDebug()
    {
        return $this->getCurrent();
    }

    public function verify(PayInfo $info)
    {
		$id = $_GET['id'] ?? $_POST['id'] ?? false;
		$order = $_GET['order_id'] ?? $_POST['order_id'] ?? false;
		if(!$id) return false;

		$dataa = [
			'id' => $id,
			'order_id' => $order,
		];
		$jsonData = json_encode($dataa);
		$headers = [
		  'Content-Type: application/json',
		  'X-API-KEY: ' . $this->key,
		];
		if($this->debug) $headers[] = 'X-SANDBOX: 1';

		$ch = curl_init("https://api.idpay.ir/v1.1/payment/verify");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		$err 	= curl_error($ch);
		curl_close($ch);

		$result = json_decode($result, true);

		if($err) {
			return false;
		}
		else {
			if($result['error_code'] ?? false)
            {
                return false;
            }
			else
            {
				$info->ref = $result['payment']['track_id'];
				return true;
			}
		}
    }

	public function getErrorText($error_id)
	{
		return "ERROR";
	}

}
