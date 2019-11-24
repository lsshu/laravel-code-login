<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/11/23
 * Time: 17:48
 */
?>
@extends('logins::layouts.main',['title'=>'账户列表'])

@section('style')
    <style>
        .container{padding:0;}
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title">选择你登录的账号</h3>
            </div>
            <div class="panel-body">
                <table class="table table-hover" >
                    {{--<caption>{{$wechatUser->nickname}}</caption>--}}
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Operate</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($wechatUser->users as $user)
                    <tr>
                        <th scope="row">{{$user->id}}</th>
                        <td>{{$user->name}}</td>
                        <td>{{$user->username}}</td>
                        <td><button class="btn btn-success btn-sm login-button" data-userid="{{$user->id}}" type="button">Login</button></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <p style="text-align: right">没有想要登录的账号,可以前往 <a href="{{$redirect_register_url}}">注册</a></p>
            </div>
            {{--<div class="panel-footer">--}}
                {{--<h3 class="panel-title"> 其它信息</h3>--}}
            {{--</div>--}}
            {{--<div class="panel-body">--}}
                {{--<ol>--}}
                    {{--<li></li>--}}
                {{--</ol>--}}
            {{--</div>--}}
        </div>

    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function(){
            toastr.options.positionClass = 'toast-top-center';
            $('.login-button').click(function(e){
                var userid =e.target.dataset.userid;
                $.post('{{$logins_url}}',{_token:"{{csrf_token()}}",userid:userid},function(responce){
                    try{
                        if(responce.status == 'success'){
                            toastr.success(responce.description, responce.title)
                        }else{
                            toastr.error(responce.description, responce.title)
                        }
                    }catch (e) {
                        console.log(e);
                    }
                })
            });


            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                //success:$.fn.lsshufunc.alert,/*默认ajax成功返回*/
                error:function(){
                    // console.log('Error !');
                    //$.fn.lsshufunc.error({title:"Error !",txt:"Error !"});
                },
                statusCode: {
                    404: function (options) {
                        $.lsshufunc.error({title:"Error !",description:"page not found 404"});
                    },
                    419:function (options) {
                        $.lsshufunc.error({title:"Error !",description:options.responseJSON.message || "请设置csrf 419"});
                    },
                    500:function (options) {
                        $.lsshufunc.error({title:"Error 服务器错误 ! 500",description:options.responseJSON.message || "500"});
                    },
                }
            });
            $.lsshufunc={
                alert:function(options){
                    eval('toastr.'+options.status+'(options.description,options.title);');
                    if(options.url){
                        setTimeout(function(){location.href=options.url;},options.settime);
                    }
                },
                error:function(options){
                    toastr.error(options.description,options.title);
                }
            };
        });
    </script>
@endsection
