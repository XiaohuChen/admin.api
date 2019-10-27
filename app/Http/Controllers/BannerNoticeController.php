<?php

namespace App\Http\Controllers;

use App\Exceptions\ArException;
use App\Models\BannerModel;
use App\Models\NoticeModel;
use App\Models\QiniuConfigModel;
use Illuminate\Http\Request;
use App\Models\NoticeModel as Notice;
use App\Models\BannerModel as Banner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BannerNoticeController extends Controller
{

    /**
     * 用户协议 修改
     */
    public function MemberDocEdit(Request $request){
        $content = $request->input('content');
        if(empty($content))
            throw new ArException(ArException::SELF_ERROR,'请填写内容');

        $data = DB::table('MemberDoc')->first();
        if(empty($data)){
            DB::table('MemberDoc')->insert([
                'MemberDoc' => $content
            ]);
        } else {
            DB::table('MemberDoc')->update(['MemberDoc' => $content]);
        }
        return self::returnMsg([], '', 20000);  
    }

    /**
     * 用户协议
     */
    public function MemberDoc(Request $request){
        $data = DB::table('MemberDoc')->first();
        if(empty($data)) return self::returnMsg(['MemberDoc' => ''], '', 20000);
        return self::returnMsg(['MemberDoc' => $data->MemberDoc], '', 20000); 
    }

     /**
     * 关于我们 修改
     */
    public function AboutUsEdit(Request $request){
        $content = $request->input('content');
        if(empty($content))
            throw new ArException(ArException::SELF_ERROR,'请填写内容');

        $data = DB::table('MemberDoc')->first();
        if(empty($data)){
            DB::table('MemberDoc')->insert([
                'AboutUs' => $content
            ]);
        } else {
            DB::table('MemberDoc')->update(['AboutUs' => $content]);
        }
        return self::returnMsg([], '', 20000);  
    }

    /**
     * 关于我们
     */
    public function AboutUs(Request $request){
        $data = DB::table('MemberDoc')->first();
        if(empty($data)) return self::returnMsg(['AboutUs' => ''], '', 20000);
        return self::returnMsg(['AboutUs' => $data->AboutUs], '', 20000); 
    }

    /**
     * @func获取Notice列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeList(Request $request)
    {
        $count = intval($request->input('limit')) > 0 ? intval($request->input('limit')) : 20;
        $where = function ($query) use ($request) {
            //筛选查询关键字
            if ($request->has('keywords') and $request->keywords != '') {
                $keywords = "%" . $request->keywords . "%";
                $query->where('Title', 'like', $keywords);
            }
        };

        $Noticelist = Notice::GetPageList($count, $where);
        foreach ($Noticelist as $key => &$value) {
            $value['AddTimeName'] = date('Y-m-d H:i:s', $value['AddTime']);
        }
        return self::returnMsg($Noticelist, '', 20000);
    }

    public function NewList(Request $request){
        $count = intval($request->input('limit')) > 0 ? intval($request->input('limit')) : 20;
        $data = DB::table('News')->paginate($count);
        $list = [];
        foreach($data as $item){
            $list[] = $item;
        }
        $res = ['list' => $list, 'total' => $data->total()];
        return self::returnMsg($res, '', 20000);
    }

    public function QaList(Request $request){
        $count = intval($request->input('limit')) > 0 ? intval($request->input('limit')) : 20;
        $data = DB::table('CommonQA')->paginate($count);
        $list = [];
        foreach($data as $item){
            $list[] = $item;
        }
        $res = ['list' => $list, 'total' => $data->total()];
        return self::returnMsg($res, '', 20000);
    }

    public function QaAdd(Request $request){
        $rules = [
            'Question'   => 'required',
            'Answer' => 'required',
        ];
        $v = Validator::make($request->all(), $rules,[
            'Question.required' => '请填写问题',
            'Answer.required' => '请填写回答'
        ]);
        if ($v->fails()) return self::returnError(20001, $v->errors()->first());
        $sqlmap = $v->validated();

        try {
            DB::table('CommonQA')->insert([
                'Question' => $sqlmap['Question'],
                'Answer' => $sqlmap['Answer']
            ]);
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    /**
     * @func更新Notice
     * @param Request $request
     */
    public function noticeAdd(Request $request)
    {
        $rules = [
            'title'   => 'required',
            'content' => 'required',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails()) return self::returnError(20001, $v->errors()->first());
        $sqlmap = $v->validated();

        $notice          = new NoticeModel();
        $notice->Title   = $sqlmap['title'];
        $notice->Content = $sqlmap['content'];
        $notice->AddTime = time();
        $notice->IsRead  = 0;

        try {
            $notice->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }

    }

    //添加快讯
    public function NewsAdd(Request $request)
    {
        $rules = [
            'title'   => 'required',
            'content' => 'required',
        ];
        $v = Validator::make($request->all(), $rules,[
            'title.required' => '请填写标题',
            'content.required' => '请填写内容'
        ]);
        if ($v->fails()) return self::returnError(20001, $v->errors()->first());
        $sqlmap = $v->validated();

        try {
            DB::table('News')->insert([
                'Title' => $sqlmap['title'],
                'Content' => $sqlmap['content'],
                'AddTime' => time()
            ]);
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }

    }

    public function QaDelete(Request $request){
        $id   = (int)$request->input('id');
        try {
            DB::table('CommonQA')->where('Id', $id)->delete();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    /**
     * @func更新Notice
     * @param Request $request
     */
    public function noticeUpdate(Request $request)
    {
        $rules = [
            'id'      => 'required|integer',
            'title'   => 'required',
            'content' => 'required',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $notice = NoticeModel::get_by_id($sqlmap['id']);
        if (!isset($notice))
            return self::errorMsg('该文章不存在');
        $notice->Title   = $sqlmap['title'];
        $notice->Content = $sqlmap['content'];
        $notice->AddTime = time();
        $notice->IsRead  = 0;

        try {
            $notice->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    //更新快讯
    public function NewsUpdate(Request $request){
        $rules = [
            'id'      => 'required|integer',
            'title'   => 'required',
            'content' => 'required',
        ];
        $v = Validator::make($request->all(), $rules,[
            'id.required' => '不存在此快讯',
            'title.required' =>  '请填写标题',
            'content.required' =>  '请填写内容'
        ]);
        if ($v->fails())
            return self::errorMsg($v->errors()->first());
        $sqlmap = $v->validated();

        try {
            DB::table('News')->where('Id', $sqlmap['id'])->update([
                'Title' => $sqlmap['title'],
                'Content' => $sqlmap['content']
            ]);
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    public function QaUpdate(Request $request){
        $rules = [
            'id'      => 'required|integer',
            'Question'   => 'required',
            'Answer' => 'required',
        ];
        $v = Validator::make($request->all(), $rules,[
            'id.required' => '不存在此快讯',
            'Question.required' =>  '请填写问题',
            'Answer.required' =>  '请填写回答'
        ]);
        if ($v->fails())
            return self::errorMsg($v->errors()->first());
        $sqlmap = $v->validated();

        try {
            DB::table('CommonQA')->where('Id', $sqlmap['id'])->update([
                'Question' => $sqlmap['Question'],
                'Answer' => $sqlmap['Answer']
            ]);
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }


    /** @func删除Notice
     * @param Request $request
     */
    public function noticeDelete(Request $request)
    {
        $id   = (int)$request->input('id');
        $data = Notice::get_by_id($id);
        if (!isset($data))
            return self::errorMsg('该文章不存在');

        $data->IsDel = 1;
        try {
            $data->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    //删除快讯
    public function NewsDelete(Request $request){
        $id   = (int)$request->input('id');

        try {
            DB::table('News')->where('Id', $id)->delete();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }

    /**
     * @func根据id查找数据
     */
    public function getNotice(Request $request)
    {
        $id   = (int)$request->input('id');
        $data = NoticeModel::get_by_id($id);
        return self::returnMsg($data);
    }

    public function GetQA(Request $request){
        $id   = (int)$request->input('id');
        $data = DB::table('CommonQA')->where('Id', $id)->first();
        return self::returnMsg($data);
    }

    //获取快讯
    public function GetNews(Request $request){
        $id   = (int)$request->input('id');
        $data = DB::table('News')->where('Id', $id)->first();
        return self::returnMsg($data);
    }


    /**
     * @func获取banner列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bannerList(Request $request)
    {
        $count      = intval($request->input('limit')) > 0 ? intval($request->input('limit')) : 10;
        $where      = function ($query) use ($request) {
            //筛选查询关键字
            if ($request->has('keywords') and $request->keywords != '') {
                $keywords = "%" . $request->keywords . "%";
                $query->where('Title', 'like', $keywords);
            }
        };
        $bannerlist = Banner::GetPageList($count, $where);
//        foreach ($bannerlist as $key => &$value) {
//            $value['Image'] = $this->config['Domain'] . '/' . $value['Image'];
//        }
        return self::returnMsg($bannerlist, '', 20000);
    }


    /**
     * @func更新banner
     * @param Request $request
     */
    public function bannerUpdate(Request $request)
    {

        $rules = [
            'Sort'  => 'required|integer',
            'Title' => 'required',
            'Image' => 'required',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $id = (int)$request->input('Id');
        if ($id > 0) {
            $banner = BannerModel::get_by_id($id);
            if (!isset($banner))
                return self::errorMsg('该banner不存在');
        } else {
            $banner = new BannerModel();
        }

        $banner->Title = $sqlmap['Title'];
        $banner->Sort  = $sqlmap['Sort'];
        $banner->Image = $sqlmap['Image'];
        $banner->IsDel = 0;
        try {
            $banner->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }


    /** @func删除banner
     * @param Request $request
     */
    public function bannerDelete(Request $request)
    {
        $id = (int)$request->input('id');
        $banner_arr = BannerModel::get_by_id($id);
        if (!isset($banner_arr))
            return self::errorMsg('数据不存在,或者已经被删除');

        $banner_arr->IsDel = 1;
        try {
            $res = $banner_arr->save();
            if ($res) {
                //删除七牛云的物理图片
            }
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }


    /**
     * @func根据id查找数据
     */
    public function getBanner(Request $request)
    {
        $id = (int)$request->input('id');
        if (empty($id))
            return self::returnMsg([], 'id不能为空', 20003);

        $banner_arr          = Banner::get_by_id($id);
        $banner_arr['Image'] = $this->config['Domain'] . '/' . $banner_arr['Image'];
        return self::returnMsg($banner_arr);
    }


}
