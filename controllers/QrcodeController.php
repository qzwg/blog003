<?php
namespace controller;
use Endroid\QrCode\QrCode;
class QrCodeController
{
    public function qrcode()
    {
        $str = $_GET['code'];
        $qrCode = new QrCode($str);
        header('Content-type:' . $qrCode->getContentType());
        echo $qrCode->writeString();
    }
}