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
?>
<!DOCTYPE html>
<html>
<head><title>  {{$code}} </title>
    <style>
        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            color: #B0BEC5;
            display: table;
            font-weight: 100;
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        .title {
            font-size: 72px;
            margin-bottom: 40px;
        }

        .t-msg {

        }

        .t-file {

        }

        .t-line {

        }

        .t-class {

        }

        .t-func {

        }

        .t-args {

        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title"> {{$code}} </div>
        <div style="display: {{ $ctrl->_has('_d') || stripos($ctrl->getRequest()->getRequestUri(), '?_d') || stripos($ctrl->getRequest()->getRequestUri(), '&_d') ? 'block' : 'none' }};">
            <h2 class="t-msg"> {{$ex_msg}}({{$ex_type}})</h2>
            @foreach($trace_list as $trace)
                <p>
                    <b class="t-file">{{$trace['file_str']}}</b>:<b class="t-line">{{$trace['line']}}</b> - <b
                            class="t-class">{{$trace['class_str']}}</b><b class="t-func">{{$trace['function']}}</b>(<b
                            class="t-args">{{$trace['args_str']}}</b>)
                </p>
            @endforeach
        </div>
    </div>
</div>
</body>
</html>