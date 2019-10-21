<?php

namespace App\Http\Controllers;

use App\Models\ArticleCateModel;
use Illuminate\Http\Request;
use App\Models\ArticleModel;
use App\Libraries\TreeUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{


    /**
     * @func获取文章分类列表
     */
    public function articleCateList(Request $request)
    {

        $bannerlist = ArticleCate::GetPageList($request->get('count'));
        foreach ($bannerlist as $key => &$value) {
            $value['PidName'] = ArticleCate::GetCateName((int)$value['Pid']);
        }
        return self::returnMsg($bannerlist, '', 20000);
    }


    /**
     * @func 修改文章分类
     * @param Request $request
     */
    public function updateCate(Request $request)
    {

        $rules = [
            'pid'  => 'required|integer',
            'name' => 'required',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();
        $id     = (int)$request->input('id');

        $data = ArticleCateModel::GetBId($id);
        if (!isset($data))
            return self::errorMsg('该分类不存在');

        $data->Pid         = $sqlmap['pid'];
        $data->Name        = $sqlmap['Name'];
        $data->Update_time = time();
        try {
            $data->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }


    /**
     * @func 删除分类
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCate(Request $request)
    {
        $id   = (int)trim($request->input('id'));
        $data = ArticleCate::GetBId($id);
        if (!isset($data))
            return self::errorMsg('数据不存在');
        $countlist = ArticleCateModel::getPidListCount($id);
        if (isset($countlist))
            return self::errorMsg('请删除下面的子分类');

        $data->IsDel = 1;
        try {
            $data->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }


    /**
     * @func 添加分类
     */
    public function addCate(Request $request)
    {
        $rules = [
            'pid'  => 'required|integer',
            'name' => 'required',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap = $v->validated();

        $data              = new ArticleCateModel();
        $data->Pid         = $sqlmap['pid'];
        $data->Name        = $sqlmap['name'];
        $data->Update_time = time();
        $data->IsDel       = 0;
        try {
            $data->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }


    /**
     * @func 获取全部数据
     */
    private function getCateDate()
    {
        return ArticleCateModel::GetList();
    }

    /**
     * @func获取树形分类
     */
    public function getCate()
    {
        $catelist     = $this->getCateDate();
        $tree         = new TreeUtil();
        $treeCateList = $tree->array2tree($catelist);
        if (empty($treeCateList)) {
            return self::returnMsg([], '没有数据', 20000);
        } else {
            return self::returnMsg($treeCateList, '操作成功', 20000);
        }
    }


    /**
     * @func获取文章列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function articleList(Request $request)
    {

        //多条件查询
        $where       = function ($query) use ($request) {
            //筛选查询关键字
            if ($request->has('keywords') and $request->keywords != '') {
                $keywords = "%" . $request->keywords . "%";
                $query->where('Title', 'like', $keywords);
            }
            if ($request->has('cateid') and $request->cateid != '') {
                $cateid = $request->cateid;
                $query->where('cateId', '=', $cateid);
            }
        };
        $articleList = ArticleModel::GetPageList($request->get('count'), $where);

        foreach ($articleList as $key => &$value) {
            $cateName = ArticleCateModel::where('id', '=', $value['Cateid'])->value('Name');
            if ($cateName) {
                $value['CateName'] = $cateName;
            } else {
                $value['CateName'] = '没有找到';
            }
            $value['Create_times'] = date('Y-m-d H:i:s', $value['Create_time']);
        }
        return self::returnMsg($articleList, '', 20000);
    }


    /**
     * @func添加文章
     * @param Request $request
     */
    public function addArticle(Request $request)
    {
        $rules = [
            'cateId'  => 'required|integer|min:1',
            'title'   => 'required',
            'content' => 'required',
            'img'     => 'required',
            'sort'    => 'required|integer',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap   = $v->validated();
        $cateList = ArticleCateModel::GetBId($sqlmap['cateId']);
        if (!isset($cateList))
            return self::errorMsg('该分类不存在');

        $data              = new ArticleModel();
        $data->Cateid      = $sqlmap['cateId'];
        $data->Title       = $sqlmap['title'];
        $data->Content     = $sqlmap['content'];
        $data->Img         = $sqlmap['img'];
        $data->Sort        = $sqlmap['sort'];
        $data->Create_time = time();
        $data->Update_time = time();
        $data->IsDel       = 0;

        try {
            $data->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }


    /**
     * @func更新文章
     * @param Request $request
     */
    public function updateArticle(Request $request)
    {

        $rules = [
            'cateId'  => 'required|integer|min:1',
            'title'   => 'required',
            'content' => 'required',
            'img'     => 'required',
            'sort'    => 'required|integer',
        ];
        $v     = Validator::make($request->all(), $rules);
        if ($v->fails())
            return self::errorMsg($v->errors());
        $sqlmap   = $v->validated();
        $cateList = ArticleCateModel::GetBId($sqlmap['cateId']);
        if (!isset($cateList))
            return self::errorMsg('该分类不存在');
        $id = (int)$request->input('id');

        $data = ArticleModel::GetBId($id);
        if (!isset($data))
            return self::errorMsg('没有找到该文章');
        $data->Cateid      = $sqlmap['cateId'];
        $data->Title       = $sqlmap['title'];
        $data->Content     = $sqlmap['content'];
        $data->Img         = $sqlmap['img'];
        $data->Sort        = $sqlmap['sort'];
        $data->Create_time = time();
        $data->Update_time = time();
        $data->IsDel       = 0;

        try {
            $data->save();
            return self::successMsg();
        } catch (\Exception $exception) {
            return self::errorMsg($exception->getMessage());
        }
    }


    /** @func删除文章
     * @param Request $request
     */
    public function deleteArticle(Request $request)
    {
        $id = (int)$request->input('id');
        if (empty($id))
            return self::returnMsg([], 'id不能为空', 20003);

        $data = ArticleModel::GetBId($id);
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


    /**
     * @func根据id查找数据
     */
    public function getArticle(Request $request)
    {
        $id = (int)trim($request->input('id'));
        if (empty($id))
            return self::returnMsg([], 'id不能为空', 20003);

        $banner_arr = ArticleModel::GetBId($id);
        if ($banner_arr)
            return self::returnMsg($banner_arr, '操作成功', 20000);
        return self::returnMsg([], '数据不存在，或以被删除', 20010);

    }
}
