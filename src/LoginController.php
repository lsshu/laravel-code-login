<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/11/23
 * Time: 16:22
 */

namespace Lsshu\LaravelCodeLogin;
use Illuminate\Http\Request;

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
            $request->session()->put('login_string', $string);
        }else{
            $string = $request->session()->get('login_string');
        }
        /*生成二维码*/
        $code = QrCode::format('png')->size(500)->generate(route(config('logins.route.name.code_auth_login','auth_login'),['login_string'=>$string,'path'=>$path]));
        $check_login = route(config('logins.route.name.check_login','check_login'),['login_string'=>$string,'path'=>$path]);
        return view('logins::login.login',compact('code','string','check_login'));
    }
}