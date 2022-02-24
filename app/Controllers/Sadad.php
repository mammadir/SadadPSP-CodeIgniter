<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Sadad extends BaseController
{
    public function index()
    {
        $sadad = new \CodeIgniter\sadad\sadad();
        return $sadad->create_payment(10000,0007); // $sadad->create_payment(price,orderid) Return PaymentLink
    }

    public function verify()
    {
        $result = $this->request->getPost(['ResCode','OrderId','token']);
        $sadad = new \CodeIgniter\sadad\sadad();
        if ($result['ResCode'] == 0) {
            $verify = $sadad->verify($result['token']);
        }
        if ($result['ResCode'] != -1 && $result['ResCode'] == 0) {
            /*
             * Save this Data To DataBase
             * --------------------------
             * $result->RetrivalRefNo
             * $result->SystemTraceNo
             * $result->OrderId
             */
            echo "شماره سفارش:" . $result['OrderId'] . "<br>" . "شماره پیگیری : " . $verify->SystemTraceNo . "<br>" . "شماره مرجع:" .
                $verify->RetrivalRefNo . "<br> اطلاعات بالا را جهت پیگیری های بعدی یادداشت نمایید." . "<br>";
        }
        else
            echo "تراکنش نا موفق بود در صورت کسر مبلغ از حساب شما حداکثر پس از 72 ساعت مبلغ به حسابتان برمی گردد.";
    }
}
