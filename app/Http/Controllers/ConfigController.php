<?php

namespace App\Http\Controllers;

use App\Models\CoinModel;
use App\Models\ConfigModel;
use App\Models\SMSConfigModel;
use App\Models\UpdateInfoModel;
use Illuminate\Http\Request;
use App\Models\QiniuConfigModel as Qiniu;
use App\Models\SMSConfigModel as SMS;
use App\Models\UpdateInfoModel as APPVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{


    /**
     * 获取七牛的配置
     */
    public function list()
    {
        $qiniuconfig = Qiniu::first();
        return self::returnMsg($qiniuconfig);
    }

    /**
     * 添加/添加七牛的配置
     */
    public function updateAddQiniu(Request $request)
    {
        $rules = [
            'domain'    => 'required',
            'bucket'    => 'required',
            'accesskey' => 'required',
            'secretkey' => 'required',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $qiniu            = Qiniu::first();
        $qiniu->Domain    = $sqlmap['domain'];
        $qiniu->Bucket    = $sqlmap['bucket'];
        $qiniu->AccessKey = $sqlmap['accesskey'];
        $qiniu->SecretKey = $sqlmap['secretkey'];

        try {
            $qiniu->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }


    //短信配置
    public function smsList()
    {
        $qiniuconfig = SMS::GetPageList();
        return self::returnMsg($qiniuconfig);
    }


    //更新或者插入短信配置
    public function updateAddSms(Request $request)
    {
        $rules = [
            'account'         => 'required',
            'password'        => 'required',
            'signName'        => 'required',
            'mold'            => 'required|integer',
            'vaildCodeLength' => 'required|integer',
            'timeOut'         => 'required|integer',
            'errorCount'      => 'required|integer',

        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $sms                  = SMSConfigModel::first();
        $sms->Account         = $sqlmap['account'];
        $sms->Password        = $sqlmap['password'];
        $sms->SignName        = $sqlmap['signName'];
        $sms->Mold            = $sqlmap['mold'];
        $sms->VaildCodeLength = $sqlmap['vaildCodeLength'];
        $sms->TimeOut         = $sqlmap['timeOut'];
        $sms->ErrorCount      = $sqlmap['errorCount'];
        $sms->Account         = $sqlmap['account'];

        try {
            $sms->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }


    /**
     * 短信常量配置
     * @return \Illuminate\Http\JsonResponse
     */
    public function smstype()
    {
        $sms_array = [
            [
                'id'    => 1,
                'title' => '阿里云短信',
            ],
            [
                'id'    => 2,
                'title' => '互易短信',
            ],
            [
                'id'    => 3,
                'title' => '聚合短信',
            ],
            [
                'id'    => 4,
                'title' => '253短信',
            ],
            [
                'id'    => 5,
                'title' => '创瑞短信',
            ],
        ];
        return self::returnMsg($sms_array, '操作成功', 20000);
    }


    /**
     * app配置列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function appList()
    {
        $data = APPVersion::GetPageList();
        return self::returnMsg($data);
    }


    //更新或者插入版本配置
    public function updateAddAppVersion(Request $request)
    {

        $rules = [
            'ver'         => 'required|numeric',
            'needInstall' => 'required|integer',
            'mustUpdate'  => 'required|integer',
            'IOS'         => 'required',
            'android'     => 'required',
            'tip'         => 'required',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $up              = UpdateInfoModel::first();
        $up->ver         = $sqlmap['ver'];
        $up->NeedInstall = $sqlmap['needInstall'];
        $up->MustUpdate  = $sqlmap['mustUpdate'];
        $up->IOS         = $sqlmap['IOS'];
        $up->Android     = $sqlmap['android'];
        $up->Tip         = $sqlmap['tip'];
        try {
            $up->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }


}
