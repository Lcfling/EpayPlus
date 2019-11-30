<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <title>网站后台管理系统</title>
    <link rel="stylesheet" type="text/css" href="/static/admin/layui/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="/static/admin/css/admin.css"/>
</head>
<body>
<div class="wrap-container welcome-container">
    <div class="row">
        <div class="welcome-left-container col-lg-9">
            <div class="data-show">
                <ul class="clearfix">
                    <li class="col-sm-12 col-md-4 col-xs-12">
                        <a href="javascript:;" class="clearfix">
                            <div class="icon-bg bg-org f-l">
                                <span class="iconfont">&#xe600;</span>
                            </div>
                            <div class="right-text-con">
                                <p class="name">今日订单数</p>
                                <p><span class="color-org">{{$data['total']}}单</span></p>
                            </div>
                        </a>
                    </li>
                    <li class="col-sm-12 col-md-4 col-xs-12">
                        <a href="javascript:;" class="clearfix">
                            <div class="icon-bg bg-blue f-l">
                                <span class="iconfont">&#xe602;</span>
                            </div>
                            <div class="right-text-con">
                                <p class="name">今日成功订单</p>
                                <p><span class="color-blue">{{$data['done']}}单</span></p>
                            </div>
                        </a>
                    </li>
                    <li class="col-sm-12 col-md-4 col-xs-12">
                        <a href="javascript:;" class="clearfix">
                            <div class="icon-bg bg-green f-l">
                                <span class="iconfont">&#xe605;</span>
                            </div>
                            <div class="right-text-con">
                                <p class="name">今日未付订单</p>
                                <p><span class="color-green">{{$data['none']}}单</span></p>
                            </div>
                        </a>
                    </li>
                    <li class="col-sm-12 col-md-4 col-xs-12">
                        <a href="javascript:;" class="clearfix">
                            <div class="icon-bg bg-green f-l">
                                <span class="iconfont">&#xe605;</span>
                            </div>
                            <div class="right-text-con">
                                <p class="name">今日成功率</p>
                                <p><span class="color-green">{{$data['done_rate']}}%</span></p>
                            </div>
                        </a>
                    </li>
                    <li class="col-sm-12 col-md-4 col-xs-12">
                        <a href="javascript:;" class="clearfix">
                            <div class="icon-bg bg-green f-l">
                                <span class="iconfont">&#xe605;</span>
                            </div>
                            <div class="right-text-con">
                                <p class="name">今日成交金额</p>
                                <p><span class="color-green">{{$data['done_money']}}元</span></p>
                            </div>
                        </a>
                    </li>
                    <li class="col-sm-12 col-md-4 col-xs-12">
                        <a href="javascript:;" class="clearfix">
                            <div class="icon-bg bg-green f-l">
                                <span class="iconfont">&#xe605;</span>
                            </div>
                            <div class="right-text-con">
                                <p class="name">累计成交金额</p>
                                <p><span class="color-green">{{$data['all_done_money']}}元</span></p>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <!--服务器信息-->
            <div class="server-panel panel panel-default">
                <div class="panel-header">最近七天订单数据</div>
                <div style="padding-left: 15px">
                    <h3 class="font-bold no-margins" style="padding-bottom: 10px;">全部/成功</h3>
                    <table style="color:#545454">
                        <tbody>
                        <tr>
                            <td class="legendColorBox">
                                <div style="border:1px solid #000000;padding:1px">
                                    <div style="width:4px;height:0;border:5px solid #DCDCDC;overflow:hidden"></div>
                                </div>
                            </td>
                            <td class="legendLabel">全部订单</td>
                        </tr>
                        <tr>
                            <td class="legendColorBox">
                                <div style="border:1px solid #000000;padding:1px">
                                    <div style="width:4px;height:0;border:5px solid #1E90FF;overflow:hidden"></div>
                                </div>
                            </td>
                            <td class="legendLabel">成功订单</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="panel-body clearfix">
                    <div>
                        <canvas id="lineChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
<script src="/static/admin/layui/layui.js" type="text/javascript" charset="utf-8"></script>
<script src="/static/admin/js/jquery-2.1.1.js" type="text/javascript" charset="utf-8"></script>
<script src="/static/admin/js/Chart.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/static/admin/lib/echarts/echarts.js"></script>
<script>
    //y1金额y2人数
    $(document).ready(function() {
        var lineData = {
            labels: {!!$data['x']!!},
            datasets: [
                {
                    label: "y1",
                    fillColor: "rgba(220,220,220,0.5)",
                    strokeColor: "rgba(220,220,220,1)",
                    pointColor: "rgba(220,220,220,1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(220,220,220,1)",
                    data: {!!$data['y1']!!},

                },
                {
                    label: "y2",
                    fillColor: "rgba(30,144,255,0.5)",
                    strokeColor: "rgba(30,144,255,0.7)",
                    pointColor: "rgba(30,144,255,1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(30,144,255,1)",
                    data: {!!$data['y2']!!},

                }
            ],


        };

        var lineOptions = {
            scaleShowGridLines: true,
            scaleGridLineColor: "rgba(0,0,0,.05)",
            scaleGridLineWidth: 1,
            bezierCurve: true,
            bezierCurveTension: 0.4,
            pointDot: true,
            pointDotRadius: 4,
            pointDotStrokeWidth: 1,
            pointHitDetectionRadius: 20,
            datasetStroke: true,
            datasetStrokeWidth: 2,
            datasetFill: true,
            responsive: true,

        };

        var ctx = document.getElementById("lineChart").getContext("2d");
        var myNewChart = new Chart(ctx).Line(lineData, lineOptions);

    });
</script>
</body>
</html>