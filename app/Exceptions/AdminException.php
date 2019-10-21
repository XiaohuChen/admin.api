<?php


namespace App\Exceptions;

//自定义异常类，方便全局异常处理
class AdminException extends \Exception
{

    const TOKEN_ERROR = 10001;
    const AUTH_ERROR = 10002;
    const ADMIN_ERROR = 10003;

    const PARAMS_ERROR = 20001;

    protected $msgList = [
        self::TOKEN_ERROR => '登录令牌验证失败，请重新登录',
        self::PARAMS_ERROR => '参数错误',
        self::AUTH_ERROR => '权限不足',
        self::ADMIN_ERROR => '管理员不存在或已禁用'
    ];

    public function __construct(int $code = 10002, string $msg = null)
    {
        $this->message = $msg;
        if ($msg === null) $this->message = $this->msgList[$code];
        $this->code = $code;
    }

}