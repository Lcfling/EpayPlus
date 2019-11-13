@section('title', '码商')
@section('header')
    <div class="layui-inline">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>六种按钮主题</legend>
            <br>
            <blockquote class="layui-elem-quote layui-text" style="veritical-align:middle;">
                <label style="font-size: 15px;">今日成功交易总额&nbsp;:&nbsp;</label><span class="label label-success">11.00元</span>
                <label style="font-size: 15px;">&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
            </blockquote>
            <br>
        </fieldset>
    </div>

    <div class="layui-inline">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>六种按钮主题</legend>
            <br>
            <blockquote class="layui-elem-quote layui-text" style="veritical-align:middle;">
                <label style="font-size: 15px;">今日成功交易总额&nbsp;:&nbsp;</label><span class="label label-success">11.00元</span>
                <label style="font-size: 15px;">&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
            </blockquote>
            <br>
        </fieldset>
    </div>

    <div class="layui-inline">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>六种按钮主题</legend>
            <br>
            <blockquote class="layui-elem-quote layui-text" style="veritical-align:middle;">
                <label style="font-size: 15px;">今日成功交易总额&nbsp;:&nbsp;</label><span class="label label-success">11.00元</span>
                <label style="font-size: 15px;">&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
            </blockquote>
            <br>
        </fieldset>
    </div>

    <div class="layui-inline">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>六种按钮主题</legend>
            <br>
            <blockquote class="layui-elem-quote layui-text" style="veritical-align:middle;">
                <label style="font-size: 15px;">今日成功交易总额&nbsp;&nbsp;</label><span class="label label-success">11.00元</span>
                <label style="font-size: 15px;">&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</label>
            </blockquote>
            <br>
        </fieldset>
    </div>


@endsection
@section('table')

@endsection
@section('js')
    <script>
        layui.use(['form', 'jquery','laydate', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer
            ;
            laydate({istoday: true});
            form.render();
            form.on('submit(formDemo)', function(data) {
            });
        });
    </script>
@endsection
@extends('common.list')
