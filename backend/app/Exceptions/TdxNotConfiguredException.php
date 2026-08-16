<?php

namespace App\Exceptions;

use Exception;

class TdxNotConfiguredException extends Exception
{
    protected $message = '尚未設定 TDX_CLIENT_ID / TDX_CLIENT_SECRET，請至交通部 TDX 平臺（https://tdx.transportdata.tw/user/dataservice/apply）申請免費金鑰後設定於後端環境變數。';
}
