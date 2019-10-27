<?php
/**
 * Created by PhpStorm.
 * User: ChenJulong
 * Date: 2019/8/26
 * Time: 10:00
 */

namespace App\Http\Controllers;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class QiniuController extends Controller
{


    /**
     * Notes:获取七牛TOKEN给前端进行图片上传
     */
    public function tokenGet()
    {
        $bucket         = $this->config['Bucket'];
        $accessKey      = $this->config['AccessKey'];
        $secretKey      = $this->config['SecretKey'];
        $auth           = new Auth($accessKey, $secretKey);
        $upToken        = $auth->uploadToken($bucket, null, 3600);//获取上传所需的token
        $data['Domain'] = $this->config['Domain'];
        $data['Token']  = $upToken;
        return self::returnMsg($data);
    }

    /**
     * @param $image base64图片素材
     */
    public function upload($image)
    {
        if (empty($this->config['Bucket']) || empty($this->config['AccessKey']) || empty($this->config['SecretKey'])) {
            return ['status' => false, 'data' => '七牛的配置项不完整，请完善七牛的配置'];
        }
        $bucket     = $this->config['Bucket'];
        $accessKey  = $this->config['AccessKey'];
        $secretKey  = $this->config['SecretKey'];
        $auth       = new Auth($accessKey, $secretKey);
        $upToken    = $auth->uploadToken($bucket, null, 3600);//获取上传所需的token
        $base64_str = '';
        if (strpos($image, "image/jpg")) {
            $base64_str = str_replace('data:image/jpg;base64,', '', $image);
        } elseif (strpos($image, "image/jpeg")) {
            $base64_str = str_replace('data:image/jpeg;base64,', '', $image);
        } elseif (strpos($image, "image/png")) {
            $base64_str = str_replace('data:image/png;base64,', '', $image);
        } elseif (strpos($image, "image/gif")) {
            $base64_str = str_replace('data:image/gif;base64,', '', $image);
        } elseif (strpos($image, "image/bmp")) {
            $base64_str = str_replace('data:image/bmp;base64,', '', $image);
        } else {
            return ['status' => false, 'data' => '图片类型错误'];
        }
        $result = $this->request_by_curl('https://up-z2.qiniup.com/putb64/-1', $base64_str, $upToken);
//        $result = $this->request_by_curl('http://upload.qiniu.com',$base64_str,$upToken);
        $Qiniu_result = json_decode($result, true);
        if (isset($Qiniu_result['key'])) {
            return ['status' => true, 'data' => $Qiniu_result['key']];
        } else {
            return ['status' => false, 'data' => $Qiniu_result['error']];
        }
    }


    private function request_by_curl($remote_server, $post_string, $upToken)
    {
        $headers   = [];
        $headers[] = 'Content-Type:image/png';
        $headers[] = 'Authorization:UpToken ' . $upToken;
        $ch        = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        //curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}