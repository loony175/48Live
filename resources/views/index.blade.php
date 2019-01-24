<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>口袋48成员直播</title>
    <meta name="description" content="口袋48网页版,口袋48电脑版">
    <meta name="keywords" content="口袋48网页版,口袋48电脑版">

    <link rel="stylesheet" href="{{asset('css/app.css')}}">
</head>
<body>
<div id="app">
    <div style="padding: 8px 16px;">
        <router-view></router-view>
    </div>
</div>
<script src="{{asset('js/app.js')}}"></script>
</body>
</html>
