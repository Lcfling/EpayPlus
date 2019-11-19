@section('title', '码商个人信息')
@section('content')
    <div class="layui-form layui-form-pane">
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">帐号：</label>
            <div class="layui-input-inline">
                <input type="text" value="{{$info['account']}}" class="layui-input" disabled>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">上级：</label>
            <div class="layui-input-inline">
                <input type="text" value="{{$info['pid']}}" class="layui-input" disabled>
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">身份：</label>
            <div class="layui-input-inline">
                <input type="number" value="{{$info['shenfen']}}" class="layui-input" disabled>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">微信费率：</label>
            <div class="layui-input-inline">
                <input type="text" value="{{$info['rate']}}" class="layui-input" disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 120px">支付宝费率：</label>
            <div class="layui-input-inline">
                <input type="text" value="{{$info['rates']}}" class="layui-input" disabled>
            </div>
        </div>
    </div>
@endsection
@section('id',$id)
@section('js')
    <script>
        layui.use(['form','jquery','laypage', 'layer'], function() {
            var form = layui.form(),
                layer = layui.layer,
                $ = layui.jquery;
            form.render();
            $(".layui-btn").hide();
        });
    </script>
@endsection
@extends('common.edit')
