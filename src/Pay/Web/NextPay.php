<?php
#auto-name
namespace Mmb\Pay\Web;

use Mmb\Pay\DefaultDebugMode;
use Mmb\Pay\PayDriver;

class NextPay extends PayDriver
{
    use DefaultDebugMode;
    
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
        $result_id = $this->uniqueID();
        
        $queryData = [
            'api_key' => $this->key,
            'amount' => $amount,
            'order_id' => $result_id,
            'currency' => "IRT",
            'callback_uri' => $this->callbackUrl,
        ];
        if(isset($options['phone'])) {
            $queryData['customer_phone'] = $options['phone'];
        }
        if(isset($options['fields'])) {
            $queryData['custom_json_fields'] = json_encode($options['fields']);
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://nextpay.org/nx/gateway/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($queryData),
        ));
        $res = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        if(!$res)
        {
            $this->error(400);
        }
        elseif($res['code'] == -1) {
            $trans_id = $res['trans_id'];
            return "https://nextpay.org/nx/gateway/payment/$trans_id";
        }
        else {
            $this->error($res['code']);
        }
	}
	
	/**
	 * گرفتن لیست آپشن ها
	 * @return array<string>
	 */
	protected function optionsList()
    {
        return [
            'phone',
            'fields',
        ];
	}
	
	/**
	 * یافتن اطلاعات از طریق لینک کنونی
	 * @return \Mmb\Pay\PayInfo|array|bool|int|null
	 */
	protected function getCurrent()
    {
        return $_GET['order_id'] ?? false;
	}
	
	/**
	 * تایید پرداخت فعلی از طریق درگاه
	 *
	 * @param \Mmb\Pay\PayInfo $info
	 * @return bool
	 */
	protected function verify(\Mmb\Pay\PayInfo $info)
    {
        $trans = @$_GET['trans_id'];
        $amount = @$_GET['amount'];
        if(!$trans || !$amount)
            return false;

        if($amount != $info->amount)
            return false;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://nextpay.org/nx/gateway/verify',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'api_key' => $this->key,
                'amount' => $amount,
                'trans_id' => $trans,
            ])
        ));
        $res = @json_decode(curl_exec($curl), true);
        curl_close($curl);

        if (!$res)
            $this->error(400);

        if($res['code'] == 0) {
            $info->trans = $trans;
            $info->ref = $res['Shaparak_Ref_Id'];
            $info->cart = $res['card_holder'];
            return true;
        }
        else {
            return false;
        }
	}

    public function getErrorText($error_id)
    {
        switch (abs($error_id)) {
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
                return "پاسخ دریافت شده از بانک نامعتبر است";
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
                return "ارتباط به سرور درگاه برقرار نشد";
            default:
                return "خطای تعریف نشده";
        }
    }

}
