<?php


namespace CodeIgniter\sadad;


class sadad
{
    public function __construct()
    {
        $this->key = config("Sadad")->key;
        $this->TerminalId = config("Sadad")->TerminalId;
        $this->MerchantId = config("Sadad")->MerchantId;
        $this->ReturnUrl = config("Sadad")->ReturnUrl;
    }

    private function encrypt_pkcs7($str, $key)
    {
        $key = base64_decode($key);
        $ciphertext = OpenSSL_encrypt($str, "DES-EDE3", $key, OPENSSL_RAW_DATA);
        return base64_encode($ciphertext);
    }

    private function CallAPI($url, $data = false)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json; charset=utf-8'));
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $result = curl_exec($ch);
            curl_close($ch);
            return !empty($result) ? json_decode($result) : false;
        }
        catch (Exception $ex) {
            return false;
        }
    }

    public function create_payment($price,$orderid)
    {
        $SignData = $this->encrypt_pkcs7("$this->TerminalId;$orderid;$price", "$this->key");
        $data = array(
            'TerminalId' => $this->TerminalId,
            'MerchantId' => $this->MerchantId,
            'Amount' => $price,
            'SignData' => $SignData,
            'ReturnUrl' => $this->ReturnUrl,
            'LocalDateTime' => date("m/d/Y g:i:s a"),
            'OrderId' => $orderid,
        );
        $result = $this->CallAPI('https://sadad.shaparak.ir/vpg/api/v0/Request/PaymentRequest', $data);

        if ($result->ResCode == 0) {
            $Token = $result->Token;
            $url = "https://sadad.shaparak.ir/VPG/Purchase?Token=$Token";
            return $url;
        }
        else {
            return $result->Description;
        }
    }

    public function verify($Token)
    {
        $verifyData = array(
            'Token' => $Token,
            'SignData' => $this->encrypt_pkcs7($Token, $this->key)
        );

        $result = $this->CallAPI('https://sadad.shaparak.ir/vpg/api/v0/Advice/Verify', $verifyData);
        return $result;
    }
}