<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/11/24
 * Time: 10:26
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
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">{{$title}}</h3>
            </div>
            <div class="panel-body">
                <h2>{{$content}}</h2>
                <p>{{$description}}</p>
            </div>
        </div>

    </div>
@endsection
@section('script')
    <script>
        setTimeout(function () {
            location.href = '{{$redirect_url}}';
        },5000)
    </script>
@endsection
