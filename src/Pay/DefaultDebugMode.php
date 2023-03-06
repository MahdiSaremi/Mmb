<?php
#auto-name
namespace Mmb\Pay;

trait DefaultDebugMode
{

    /**
     * ایجاد لینک پرداخت - حالت دیباگ
     * 
     * @param int $amount
     * @param array $options
     * @return string
     */
    protected function requestPayDebug($amount, $options, &$saved_data, &$result_id)
    {
        $result_id = $this->uniqueID();
        return $this->callbackUrl . "?pay=true&debug=true&id=$result_id";
    }

    /**
     * یافتن اطلاعات از طریق لینک کنونی در حالت دیباگ
     * 
     * @return int|array|PayInfo|bool|null
     */
    protected function getCurrentDebug()
    {
        if(@$_GET['pay'] == 'true' && @$_GET['debug'] == 'true')
        {
            return $_GET['id'] ?? -1;
        }
    }

    /**
     * تایید پرداخت فعلی از طریق درگاه در حالت دیباگ
     * 
     * @param PayInfo $info
     * @return bool
     */
    protected function verifyDebug(PayInfo $info)
    {
        return true;
    }
    
}
