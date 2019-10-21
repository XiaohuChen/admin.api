<?php

namespace App\Http\Middleware;

use Closure;
use App\Libraries\Send;
use App\Models\AdminUserTokenModel as AdminToken;
use App\Models\AdminUserModel as AdminUser;
use App\Exceptions\AdminException;
use App\Models\AdminRuleGroupModel;
use App\Models\AdminRulesModel;
use Illuminate\Support\Facades\DB;
use Qiniu\Http\Request;
use SebastianBergmann\Environment\Console;

class VerifyToken
{
    use Send;
    #UF997097637021
    protected static $key = 'UF((&)(&^#&)@!';

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //获取头部信息
        try {
            $token = $request->header('Authorization');   //获取请求中的Authorization字段，值形式为"随机字符串 asdsajh.."这种形式

            $authorizationInfo = explode(":", openssl_decrypt(base64_decode($token), 'DES-EDE3', self::$key, OPENSSL_RAW_DATA));  //对base_64解密，获取到用:拼接的自字符串，然后分割，可获取memberid、token这两个参数

            $clientInfo['id']    = $authorizationInfo[0];
            $clientInfo['token'] = $authorizationInfo[1];
        } catch (\Exception $e) {
            return self::returnMsg([], '登录密码错误', 10001);
        }
        $uid = self::certification($clientInfo);
        $uri = $request->getPathInfo();
        if ($uri == '/admin/adminGuge' || $uri == '/user/logout' || $uri == 'coin/getCoinList') {
            $request->attributes->add(['uid' => $uid]);
            return $next($request);
        }
        if (!$this->verifyAuth($uid, $uri, $request->all())) throw new AdminException(AdminException::AUTH_ERROR);

        if ($request->has('limit')) {
            $count = intval($request->input('limit')) > 0 ? intval($request->input('limit')) : 20;
        } else $count = 20;

        $request->attributes->add(['uid' => $uid, 'count' => $count]);

        return $next($request);
    }

    /**
     * 验证权限
     */
    public function verifyAuth(int $uid, string $uri, $params)
    {
        //查询到当前Admin账号
        $admin = AdminUser::where('Id', $uid)->where('IsDel', 0)->first();
        if (empty($admin)) throw new AdminException(AdminException::ADMIN_ERROR);

        //去除空值
        $groupIds = array_filter(explode(',', $admin->RuleGroup));

        //查询到所属权限组
        $groups = AdminRuleGroupModel::whereIn("Id", $groupIds)->get();

        $ruleIds = [];
        foreach ($groups as $group) {
            //最高权限
            if ($group->Rules == '*') return true;
            //分割规则Id
            $ids = explode(',', $group->Rules);
            //到整形
            $ids = array_map('intval', $ids);
            //合并数组
            $ruleIds = array_merge($ruleIds, $ids);
        }
        //去重
        $ruleIds = array_unique($ruleIds);

        $rules_all = AdminRulesModel::whereIn('Id', $ruleIds)->get()->toArray();
        //取出Rule字段
        $rules = array_column($rules_all, 'Rule');
        if (!in_array($uri, $rules) && $uri != '/user/info') return false;
        else {
            $key = array_search($uri, $rules);
            if ($key < 1) return true;
            if ($rules_all[$key]['IsLog'] < 1) return true;
            self::writeLog($uid, $uri, $rules_all[$key]['Name'], $params);
            return true;
        }
    }

    public static function writeLog($uid, $uri, $name, $params)
    {
        $sqlmap = [
            'Admin'  => $uid,
            'Time'   => time(),
            'Ip'     => \Illuminate\Support\Facades\Request::ip(),
            'Uri'    => $uri,
            'Name'   => $name,
            'Params' => json_encode($params, JSON_UNESCAPED_UNICODE),
        ];

        DB::table('AdminLog')->insert($sqlmap);
    }

    /**
     * 获取用户信息后 验证Token
     * @return mixed
     */
    public static function certification($data = [])
    {
        $token = AdminToken::where('Token', $data['token'])->first();
        if (!$token)
            throw new AdminException(AdminException::TOKEN_ERROR, '请先登录');

        if ($token['ExpireTime'] < time())
            throw new AdminException(AdminException::TOKEN_ERROR, '登录凭证已失效，请重新登录');

        if (intval($token['AdminId']) !== intval($data['id']))
            throw new AdminException(AdminException::TOKEN_ERROR, '登录密码错误');

        $has = AdminUser::where('Id', $data['id'])->first();
        if (empty($data))
            throw new AdminException(AdminException::TOKEN_ERROR, '该用户已失效');

        return $data['id'];
    }
}
