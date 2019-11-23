<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/8/28
 * Time: 14:46
 */

namespace Lsshu\LaravelCodeLogin;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Routing\Router;
class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->singleton(Service::class, function(){
            return Service::instance();
        });
    }
    /**
     * Register any application services.
     */
    public function boot(Router $router)
    {
        $this->registerRoute($router);
//        $this->loadViewsFrom(__DIR__.'/resources/views/vendor/code_login', 'code_login');
//        $this->publishes([
//            __DIR__.'/configs/code_login.php' => config_path('code_login.php')
//        ], 'code-login-configs');
//        $this->publishes([
//            __DIR__.'/database/migrations' => database_path('migrations')
//        ], 'code-login-migrations');
//        $this->publishes([
//            __DIR__.'/resources/views' => resource_path('views'),
//        ], 'code-login-resources');
//        $this->publishes([
//            __DIR__.'/resources/assets' => public_path('vendor/code_login')
//        ], 'code-login-assets');
    }
    /**
     * Register routes.
     *
     * @param $router
     */
    protected function registerRoute($router)
    {
        if (!$this->app->routesAreCached()) {
            $router->group(['prefix'=>'{path}'],function($router){
                $router->group(array_merge(['namespace' => __NAMESPACE__,'middleware' => 'web',], config('logins.route.options', [])), function ($router) {
                    $name = config('logins.route.name');
                    $controller = config('logins.route.controller') ?? 'LoginController';
                    $login = $name['login'] ?? 'logins';
                    $register = $name['register'] ?? 'registers';
                    $code_auth_login = $name['auth_login'] ?? 'auth_logins';
                    $check_login = $name['check_login'] ?? 'check_logins';
                    $authorize_callback = $name['authorize_callback'] ?? 'authorize_callbacks';
                    $router->get($login,$controller.'@'.$login)->name($login); // 登录
                    $router->get($register,$controller.'@'.$register)->name($register); // 注册页面
                    $router->post($register,$controller.'@'.$register)->name($register); // 注册操作
                    $router->get($code_auth_login.'/{login_string}', $controller.'@'.$code_auth_login)->name($code_auth_login); // 授权登录
                    $router->get($check_login.'/{login_string}', $controller.'@'.$check_login)->name($check_login); // 检查是否登录
                    $router->get($authorize_callback, $controller.'@'.$authorize_callback)->name($authorize_callback); // 微信授权回调
                });
            });
        }
    }
}