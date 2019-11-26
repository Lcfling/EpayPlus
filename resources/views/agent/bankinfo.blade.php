@section('title', '添加商户')
@section('content')
    <div class="layui-form layui-form-pane">
    <div class="layui-form-item">
        <label class="layui-form-label">代理商名：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['agent_name'] or ''}}" class="layui-input" disabled>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">账号：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['account'] or ''}}" class="layui-input" disabled>
        </div>
    </div>
        <blockquote class="layui-elem-quote layui-text"></blockquote>
    @foreach($bank as $list)
        <div class="layui-form-item">
            <label class="layui-form-label">姓名：</label>
            <div class="layui-input-inline">
                <input type="text" value="{{$list['name'] or ''}}" class="layui-input" disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">银行卡号：</label>
            <div class="layui-input-inline">
                <input type="text" value="{{$list['deposit_card'] or ''}}" class="layui-input" disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">银行名称：</label>
            <div class="layui-input-inline">
                <input type="text" value="{{$list['deposit_name'] or ''}}" class="layui-input" disabled>
            </div>
        </div>
        <blockquote class="layui-elem-quote layui-text"></blockquote>
    @endforeach
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
