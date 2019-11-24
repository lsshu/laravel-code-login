<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/11/23
 * Time: 16:22
 */

namespace Lsshu\LaravelCodeLogin;
use Lsshu\LaravelCodeLogin\models\WechatUserInfo;
use Illuminate\Http\Request;
use QrCode;

class LoginController extends Controller
{
    /**
     * 登录页面
     * @param Request $request
     * @param string $path
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function logins(Request $request, $path='admin')
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }
        /*获取登录字符串*/
        if (!$request->session()->exists('login_string')) {
            $string = getRandString(50);
            $this->setStore($string,0);
            $this->setStore($string.'_status',0); // 扫码状态
            $request->session()->put('login_string', $string);
        }else{
            $string = $request->session()->get('login_string');
        }
        /*生成二维码*/
        $code = QrCode::format('png')->size(500)->generate(route(config('logins.route.name.auth_login','auth_logins'),['login_string'=>$string,'path'=>$path]));
        $check_login = route(config('logins.route.name.check_login','check_logins'),['login_string'=>$string,'path'=>$path]);
        return view('logins::login.login',compact('code','string','check_login','path'));
    }

    /**
     * 登录页面
     * @param Request $request
     * @param string $path
     * @param $login_string
     * @return \Illuminate\Http\JsonResponse
     */
    public function logins_post(Request $request,$path='admin', $login_string)
    {
        if($this->getStore($login_string) !== '') {
            $this->overrideConfig($path);
            /*判断微信授权 是否已经登录*/
            if ($this->authCheckLogin()) {
                return response()->json(['status' => 'error', 'title' => '登录失败!', 'description' => '微信未有登录授权!']);
            }
            $wechatUser = WechatUserInfo::where('openid', session('wx_openid'))->first();
            if ($wechatUser) {
                $userid = $request->input('userid', 0);
                if ($wechatUser->users->isNotEmpty() && $wechatUser->users->contains('id', $userid)) {
                    $this->setStore($login_string, 1);
                    $this->setStore($login_string . '_userid', $userid);
                    return response()->json(['status' => 'success', 'title' => '登录成功', 'description' => '可以关闭此页面！']);
                }
                return response()->json(['status' => 'error', 'title' => '登录失败', 'description' => '没有这个登录用户！']);
            }
            return response()->json(['status' => 'error', 'title' => '登录失败', 'description' => '没有正常授权！']);
        }
        return response()->json(['status' => 'error', 'title' => '登录失败', 'description' => '已经登录过了,或者操作失效！']);
    }

    /**
     * 检查登录
     * @param Request $request
     * @param string $path
     * @param $login_string
     * @return \Illuminate\Http\JsonResponse
     */
    public function check_logins(Request $request,$path='admin',$login_string)
    {
        $this->overrideConfig($path);

        if('1' == $this->getStore($login_string)){
            $userid = $this->getStore($login_string.'_userid');
            $relatedModel = config('admin.database.users_model');
            $user = (new $relatedModel)->where('id',$userid)->first();
            // 登录操作
            $this->guard()->login($user,config("logins.login.$path.remember",true));

            // 清除redis session
            $this->delStore($login_string);
            $this->delStore($login_string.'_userid');
            $this->delStore($login_string.'_status');
            $request->session()->forget('login_string');
            // 返回
            return response()->json(['status'=>'success','text'=>'登录成功！','redirect'=>url($this->redirectPath())]);
        }else{
            $code_status = $this->getStore($login_string.'_status');
            return response()->json(['status'=>'error','text'=>'登录失败！','code_status'=>$code_status,'code_title'=>'已经扫码,请确定登录!']);
        }
    }

    /**
     * 授权登录
     * @param Request $request
     * @param string $path
     * @param $login_string
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function auth_logins(Request $request,$path='admin', $login_string)
    {
        if($this->getStore($login_string) !== ''){
            $this->overrideConfig($path);
            /*判断微信授权 是否已经登录*/
            if($this->authCheckLogin()){
                return $this->authLogin($path);
            }
            $wechatUser = WechatUserInfo::where('openid',session('wx_openid'))->first();
            $redirect_register_url = route(config('logins.route.name.register','registers'),['path'=>$path]);
            if($wechatUser && $wechatUser->users->isNotEmpty()){
                $this->setStore($login_string.'_status',1); // 修改扫码状态
                $logins_url = route(config('logins.route.name.login_post','logins_post'),['login_string'=>$login_string,'path'=>$path]);
                return view('logins::login.auth_logins',compact('wechatUser','logins_url','redirect_register_url'));
            } elseif ($wechatUser) { // 注册

                return view('logins::login.choose_or_register',['title'=>'授权提示！','content'=>'授权成功！','description'=>'请先注册账户！正在前往，稍等！','redirect_url'=>$redirect_register_url]);
            }else{ // 重新授权获取用户微信信息
                return $this->authLogin($path,true);
            }
        }
        return view('logins::login.obsolete');
    }

    /**
     * 默认注册页 操作
     * @param Request $request
     * @param string $path
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function registers(Request $request,$path='admin')
    {
        $this->overrideConfig($path);
        /*判断微信授权 是否已经登录*/
        if($this->authCheckLogin()){
            return $this->authLogin($path);
        }
        if ($request->isMethod('post')){
            $data = $request->only(['username','name']);
            $data['password'] = bcrypt(config("logins.login.$path.register_default_password",''));

            $relatedModel = config('admin.database.users_model');
            $user = (new $relatedModel)->create($data);

            $wechatUser = WechatUserInfo::where('openid',session('wx_openid'))->first();
            $res = $wechatUser->users()->attach($user->id,['path'=>$path]);

            if($user){
                return response()->json(['status'=>'success','title'=>'注册成功！']);
            }
            return response()->json(['status'=>'error','title'=>'注册失败！']);
        }
        $redirect_url = url($this->redirectPath());
        return view('logins::login.register',compact('redirect_url'));
    }
}