<?php
#auto-name
namespace Mmb\Pay\Web;

use Mmb\Pay\PayDriver;
use Mmb\Pay\PayInfo;

class ZarinPal extends PayDriver
{
    
	/**
	 * ایجاد لینک پرداخت
	 *
	 * @param int $amount
	 * @param array $options
	 * @param int $saved_data &$result_id
	 * @param mixed $result_id
	 * @return string
	 */
	protected function requestPay($amount, $options, &$saved_data, &$result_id)
    {
        $MerchantID = $this->key;
		$SandBox = $this->debug;
        $zarinGate = $SandBox ? false : $options['gate'] ?? false;
		$callback = $this->callbackUrl;

		$node 	= $SandBox ? "sandbox" : "ir";
		$upay 	= $SandBox ? "sandbox" : "www";

		$data = array(
			'MerchantID'     => $MerchantID,
			'Amount'         => $amount,
			'Description'    => $options['description'] ?? $options['des'] ?? "پرداخت",
			'CallbackURL'    => $callback,
		);

		$jsonData = json_encode($data);
		$ch = curl_init("https://{$upay}.zarinpal.com/pg/rest/WebGate/PaymentRequest.json");
		curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($jsonData)));

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
			$Status 	    =   $result["Status"] ?? 0;
			$Authority 	    =   $result["Authority"] ?? "";
			$StartPay 	    =   $Authority ? "https://{$upay}.zarinpal.com/pg/StartPay/$Authority" : "";
			$StartPayUrl    =   $zarinGate ? "{$StartPay}/ZarinGate" : $StartPay;
			if($Status == 100)
            {
                $result_id = $Authority;

                return $StartPayUrl;
			}
			else
			{
                $this->error(+$Status);
            }
        }
	}
	
	/**
	 * گرفتن لیست آپشن ها
	 * @return array<string>
	 */
	protected function optionsList()
    {
        return [
            'gate', 'description', 'des',
        ];
	}
	
	/**
	 * یافتن اطلاعات از طریق لینک کنونی
	 * @return \Mmb\Pay\PayInfo|array|bool|int|null
	 */
	protected function getCurrent()
    {
		return $_GET['Authority'] ?? "";
	}
	
	/**
	 * تایید پرداخت فعلی از طریق درگاه
     * @param PayInfo $info
	 * @return bool
	 */
	protected function verify(PayInfo $info)
    {
        $MerchantID = $this->key;
		$SandBox = $this->debug;

		$Authority 	= $_GET['Authority'] ?? "";
		$node       = $SandBox ? "sandbox" : "ir";
		$upay       = $SandBox ? "sandbox" : "www";
		
		$data = array('MerchantID' => $MerchantID, 'Authority' => $Authority, 'Amount' => $info->amount);
		$jsonData = json_encode($data);
		$ch = curl_init("https://{$upay}.zarinpal.com/pg/rest/WebGate/PaymentVerification.json");
		curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($jsonData)));

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
			$Status = $result["Status"] ?? 0;
			$RefID  = $result['RefID'] ?? "";
			if($Status == 100)
            {
                $info->ref = $RefID;
                // $info->cart = $result['card_holder'];
                return true;
			}
			else
            {
                return false;
                // $this->error(+$Status);
			}
		}
	}

    /**
     * گرفتن متن خطا
     * 
     * @param int $error_id
     * @return string
     */
    public function getErrorText($error_id)
    {
        switch($error_id) {
            case 0:
                return "پرداخت تکمیل و با موفقیت انجام شده است";
            case 1:
                return "منتظر ارسال تراکنش و ادامه پرداخت";
            case 2:
                return "پرداخت رد شده توسط کاربر یا بانک";
            case 3:
                return "پرداخت در حال انتظار جواب بانک";
            case 4:
                return "پرداخت لغو شده است";
            case 20:
                return "کد api_key ارسال نشده است";
            case 21:
                return "کد trans_id ارسال نشده است";
            case 22:
                return "مبلغ ارسال نشده";
            case 23:
                return "لینک ارسال نشده";
            case 24:
                return "مبلغ صحیح نیست";
            case 25:
                return "تراکنش قبلا انجام و قابل ارسال نیست";
            case 26:
                return "مقدار توکن ارسال نشده است";
            case 27:
                return "شماره سفارش صحیح نیست";
            case 28:
                return "مقدار فیلد سفارشی [custom_json_fields] از نوع json نیست";
            case 29:
                return "کد بازگشت مبلغ صحیح نیست";
            case 30:
                return "مبلغ کمتر از حداقل پرداختی است";
            case 31:
                return "صندوق کاربری موجود نیست";
            case 32:
                return "مسیر بازگشت صحیح نیست";
            case 33:
                return "کلید مجوز دهی صحیح نیست";
            case 34:
                return "کد تراکنش صحیح نیست";
            case 35:
                return "ساختار کلید مجوز دهی صحیح نیست";
            case 36:
                return "شماره سفارش ارسال نشد است";
            case 37:
                return "شماره تراکنش یافت نشد";
            case 38:
                return "توکن ارسالی موجود نیست";
            case 39:
                return "کلید مجوز دهی موجود نیست";
            case 40:
                return "کلید مجوزدهی مسدود شده است";
            case 41:
                return "خطا در دریافت پارامتر، شماره شناسایی صحت اعتبار که از بانک ارسال شده موجود نیست";
            case 42:
                return "سیستم پرداخت دچار مشکل شده است";
            case 43:
                return "درگاه پرداختی برای انجام درخواست یافت نشد";
            case 44:
                return "پاسخ دریاف شده از بانک نامعتبر است";
            case 45:
                return "سیستم پرداخت غیر فعال است";
            case 46:
                return "درخواست نامعتبر";
            case 47:
                return "کلید مجوز دهی یافت نشد [حذف شده]";
            case 48:
                return "نرخ کمیسیون تعیین نشده است";
            case 49:
                return "تراکنش مورد نظر تکراریست";
            case 50:
                return "حساب کاربری برای صندوق مالی یافت نشد";
            case 51:
                return "شناسه کاربری یافت نشد";
            case 52:
                return "حساب کاربری تایید نشده است";
            case 60:
                return "ایمیل صحیح نیست";
            case 61:
                return "کد ملی صحیح نیست";
            case 62:
                return "کد پستی صحیح نیست";
            case 63:
                return "آدرس پستی صحیح نیست و یا بیش از ۱۵۰ کارکتر است";
            case 64:
                return "توضیحات صحیح نیست و یا بیش از ۱۵۰ کارکتر است";
            case 65:
                return "نام و نام خانوادگی صحیح نیست و یا بیش از ۳۵ کاکتر است";
            case 66:
                return "تلفن صحیح نیست";
            case 67:
                return "نام کاربری صحیح نیست یا بیش از ۳۰ کارکتر است";
            case 68:
                return "نام محصول صحیح نیست و یا بیش از ۳۰ کارکتر است";
            case 69:
                return "آدرس ارسالی برای بازگشت موفق صحیح نیست و یا بیش از ۱۰۰ کارکتر است";
            case 70:
                return "آدرس ارسالی برای بازگشت ناموفق صحیح نیست و یا بیش از ۱۰۰ کارکتر است";
            case 71:
                return "موبایل صحیح نیست";
            case 72:
                return "بانک پاسخگو نبوده است لطفا با نکست پی تماس بگیرید";
            case 73:
                return "مسیر بازگشت دارای خطا میباشد یا بسیار طولانیست";
            case 90:
                return "بازگشت مبلغ بدرستی انجام شد";
            case 91:
                return "عملیات ناموفق در بازگشت مبلغ";
            case 92:
                return "در عملیات بازگشت مبلغ خطا رخ داده است";
            case 93:
                return "موجودی صندوق کاربری برای بازگشت مبلغ کافی نیست";
            case 94:
                return "کلید بازگشت مبلغ یافت نشد";
            case 400:
                return "ارتباط با سایت درگاه برقرار نشد";
            default:
                return "خطای تعریف نشده";
        }
    }

}
