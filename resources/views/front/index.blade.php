<!doctype html>
<html data-ver="{{ \app\App::config('ENV_WEB.ver') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $site_title }}</title>

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">
            {{ $site_title }}
        </div>

        <div class="links">
            <a target="_blank" href="https://learnku.com/docs/laravel/5.5/queries/1327">laravel</a>
            <a target="_blank" href="/?_bomp=1">Bomp</a>
            <a target="_blank" href="/graphiql">GraphiQL</a>
            <a target="_blank" href="/develop">Develop</a>
        </div>
    </div>
</div>
</body>
</html>
<?php
/** @var string $ex_msg */
/** @var string $ex_type */

/** @var \app\App $app */
/** @var \app\Controller $ctrl */
/** @var \app\Request $request */
/** @var array $routeInfo */
/** @var string $webname */
/** @var string $webver */
/** @var string $cdn */