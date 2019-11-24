<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/11/24
 * Time: 09:28
 */
?>
@extends('logins::layouts.main',['title'=>'提示!'])

@section('style')
    <style>
        .container{padding:0;text-align: center}
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h3 class="panel-title">错误操作!</h3>
            </div>
            <div class="panel-body">
                <h2>页面失效!</h2>
                <p>请重新按照流程登录操作!</p>
            </div>
        </div>

    </div>
@endsection
