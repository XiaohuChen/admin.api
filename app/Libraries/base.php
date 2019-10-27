<?php

namespace App\Libraries;

use App\Libraries\CoinService\CoinServiceClient;
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\TBufferedTransport;

header("Content-Type: text/html;charset=utf-8");
header("Access-Control-Allow-Origin: *");

class base
{
    public $client = null;
    public $transport = null;
    public $IIA = null;

    public function __construct($dirname, $filename)
    {
        require_once __DIR__ . DS . 'php' . DS . 'CoinServer' . DS . 'CoinService.php';
        require_once __DIR__ . DS . 'php' . DS . 'CoinServer' . DS . 'IIAService.php';
        $GEN_DIR = realpath(dirname(__FILE__)) . '/gen_php/' . $dirname;
        $loader  = new ThriftClassLoader();
        $loader->registerNamespace('Thrift', __DIR__ . 'base.php/');
        $loader->registerDefinition('TTG', $GEN_DIR);
        $loader->register();
    }

    public function to_Tsorket($ip, $port)
    {
        $socket = new TSocket($ip, $port);
        $socket->setSendTimeout(60000);
        $socket->setRecvTimeout(60000);
        $this->transport = new TBufferedTransport($socket, 1024, 1024);
        $protocol        = new TBinaryProtocol($this->transport);
        $this->client    = new CoinServiceClient($protocol);
    }

    public function get_class($class)
    {
        switch ($class) {
            case "Business":
                return new Business();
                break;
            case "businessSingle":
                return new businessSingle();
                break;
            case "BusinessSetting":
                return new BusinessSetting();
                break;
        }
    }
}


