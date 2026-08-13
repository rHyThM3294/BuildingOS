<?php

namespace App\Exceptions;

use Exception;

class CwaNotConfiguredException extends Exception
{
    protected $message = '尚未設定 CWA_API_KEY，請至中央氣象署會員專區（https://opendata.cwa.gov.tw/user/authkey）申請免費金鑰後設定於後端環境變數。';
}
