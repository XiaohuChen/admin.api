<?php
namespace App\Libraries;

use Illuminate\Support\Facades\DB;

trait Tools
{

    public $config=[];

    public function __construct(){
        $QiniuConfig = DB::table('QiniuConfig')->limit(1)->first();
        if($QiniuConfig){
            $QiniuConfig_json = json_encode($QiniuConfig);
            $QiniuConfig = json_decode($QiniuConfig_json,true);
        }else{
            $QiniuConfig = [];
        }
        $SMSConfig = DB::table('SMSConfig')->limit(1)->first();
        if($SMSConfig){
            $SMSConfig_json = json_encode($SMSConfig);
            $SMSConfig = json_decode($SMSConfig_json,true);
        }else{
            $SMSConfig = [];
        }
        $this->config = array_merge($QiniuConfig , $SMSConfig);
    }

	//生成Token
	public static function MakeToken(int $uid){
		$token = md5(microtime(true).$uid);
		return $token;
	}

	//Token加密
	public function TokenEncrypt(int $uid, $token){
		#UF997097637021
		$key = 'UF((&)(&^#&)@!';
		$encryptToken = base64_encode(openssl_encrypt("{$uid}:{$token}", 'DES-EDE3', $key, OPENSSL_RAW_DATA));
		return $encryptToken;
	}


    /**
     * @func 获取时间
     */
    public function datetime($str){
        switch($str){
            case "upmouth":
                $data['start'] = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m")-1,1,date("Y")));
                $data['end'] =  date("Y-m-d H:i:s",mktime(23,59,59,date("m") ,0,date("Y")));
                $data['mouth'] = date("m",strtotime($data['start']));
                $data['year'] =  date("Y",strtotime($data['start']));
                return $data;
                break;
            case "upupmouth":
                $data['start'] =  date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m")-2,1,date("Y")));
                $data['end'] =  date("Y-m-d H:i:s",mktime(23,59,59,date("m")-1 ,0,date("Y")));
                $data['mouth'] = date("m",strtotime($data['start']));
                $data['year'] =  date("Y",strtotime($data['start']));
                return $data;
                break;
            case "today":
                $data['start'] = date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));
                $data['end'] =  date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);
                $data['mouth'] = date("m",strtotime($data['start']));
                $data['year'] =  date("Y",strtotime($data['start']));
                return $data;
                break;
            case "yesterday":
                $data['start'] = date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')-1,date('Y')));
                $data['end'] =  date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y'))-1);
                $data['mouth'] = date("m",strtotime($data['start']));
                $data['year'] =  date("Y",strtotime($data['start']));
                return $data;
                break;
            case "tomorrow":
                $data['start'] = date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y')));
                $data['end'] =  date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+2,date('Y'))-1);
                $data['mouth'] = date("m",strtotime($data['start']));
                $data['year'] =  date("Y",strtotime($data['start']));
                return $data;
                break;
            case "lastweek":
                $data['start'] = date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y')));
                $data['end'] =  date("Y-m-d H:i:s",mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y')));
                $data['mouth'] = date("m",strtotime($data['start']));
                $data['year'] =  date("Y",strtotime($data['start']));
                return $data;
                break;
            case "thismonth":
                $data['start'] = date("Y-m-d H:i:s",mktime(0,0,0,date('m'),1,date('Y')));
                $data['end'] =  date("Y-m-d H:i:s",mktime(23,59,59,date('m'),date('t'),date('Y')));
                $data['mouth'] = date("m",strtotime($data['start']));
                $data['year'] =  date("Y",strtotime($data['start']));
                return $data;
                break;

            case 'nearweek':                     //近七天的时间
                $format='Y-m-d H:i:s';
                $time = time();
                //组合数据
                $dateweek = [];
                for ($i=1; $i<=7; $i++){
                    $dateweek[$i] = date($format ,strtotime( '+' . $i-7 .' days', $time));
                }
                $data['start']=$dateweek[1];
                $data['end']=$dateweek[7];
                $data['mouth'] = date("m",strtotime($data['start']));
                $data['year'] =  date("Y",strtotime($data['start']));
                return $data;
                break;
            case 'nearmonth':
                $time = time();
                //组合数据
                $datemonth = [];
                for($i=0;$i<30;$i++){
                    $datemonth[] = date('Y-m-d H:i:s',strtotime('-'.$i.' day', $time));
                }
                $data['start']=$datemonth[29];
                $data['end']=$datemonth[0];
                $data['mouth'] = date("m",strtotime($data['start']));
                $data['year'] =  date("Y",strtotime($data['start']));
                return $data;
            case 'thisyearmonth':    //获取今年当月之前的数据
                $time = time();
                $mouth_num = date('m',$time);
                //组合数据
                $datayear = [];
                for($i=0;$i<(int)$mouth_num;$i++){
                    $month=date('Y-m',strtotime('-'.$i.' month', $time));
                    $month_day = date('t', strtotime($month.'-'.'1'));
                    $datayear['month'][]=$month;
                    $datayear['mouth_day'][]=strtotime($month.'-'.$month_day .' 23:59:59');
                }
                return $datayear;

            case 'nearyear':    //最近几年的数据
                $time = time();
                //组合数据
                $datayear = [];
                for($i=0;$i<24;$i++){
                    $datayear[] = date('Y-m',strtotime('-'.$i.' month', $time));
                }
                return $datayear;

            case 'dayevery':    //默认往前面推两个月
                $dt_start = strtotime(date('Y-m-d',time()));
                $strtotime = time() - (60 * 86400);
                $time_end = strtotime(date('Y-m-d',$strtotime));
                $dt_end = strtotime(date('Y-m-d',$time_end));
                $days = array();
                while ($dt_start>=$dt_end){
                    $days[] = date('Y-m-d',$dt_start);
                    $dt_start = strtotime('-1 day',$dt_start);
                }
                return $days;

            default:
                return [];

        }
    }




    /**
     * @param $MemberId 用户id
     * @param $Number   装入的数量
     * @param $call_index 模型关键字
     * @param $flag 标识符 扣除锁定余额 还是 冻结余额
     * @return array|bool
     */
    public static function writeFinancing($MemberId,$Number,$call_index,$flag,$coinId=''){
        if(empty($coinId)){
            $coin = DB::table('Coin')->where('EnName', '=', 'DMA')->limit(1)->first();
        }else{
            $coin = DB::table('Coin')->where('Id', '=', $coinId)->limit(1)->first();
        }
        if(empty($coin)){
            return ['status'=>false,'msg'=>'没有找到币种信息'];
        }
        $financing = DB::table('FinancingMold')->where('call_index', '=', $call_index)->limit(1)->first();
        $members_icon_find = DB::table('MemberCoin')->where('MemberId', '=', $MemberId)->where('CoinId','=',$coin->Id)->first();              //查找用户余额
        if (empty($members_icon_find)) {
            return ['status'=>false,'msg'=>'没有找到用户余额信息'];
        }

        switch($flag){
            case 'LockMoney':
                if ($members_icon_find->LockMoney < 0) {
                    return ['status'=>false,'msg'=>'锁定余额不足'];
                }
                break;
            case 'Forzen':
                if ($members_icon_find->Forzen < 0) {
                    return ['status'=>false,'msg'=>'冻结余额不足'];
                }
                break;
        }

        $member_mode = $MemberId % 20;
        if ($member_mode < 10) {
            $table_name = "FinancingList_0" . $member_mode;
        } else {
            $table_name = "FinancingList_" . $member_mode;
        }

        $sqldata['MemberId'] = $MemberId;
        $sqldata['CoinId'] = $coin->Id;
        $sqldata['CoinName'] = $coin->EnName;
        $sqldata['AddTime'] = time();
        $sqldata['Mold'] = $financing->id;
        $sqldata['MoldTitle'] = $financing->title;
        $sqldata['Money'] = (float)$Number;
        $sqldata['Balance'] = (float)$members_icon_find->Money ?? 0;
        $sqldata['Remark'] = $financing->title;
        $financingList_result = DB::table($table_name)->insert($sqldata);
        if($financingList_result){
            return ['status'=>true,'msg'=>'写入用户数据成功'];
        }else{
            return ['status'=>false,'msg'=>'写入用户数据失败'];
        }
    }



}

