@section('title', '添加商户')
@section('content')
    <div class="layui-form-item">
        <label class="layui-form-label">商户ID：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['business_code'] or ''}}"class="layui-input" disabled>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">姓名：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['name'] or ''}}" class="layui-input" disabled>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">银行卡号：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['deposit_card'] or ''}}" class="layui-input" disabled>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">银行名称：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['deposit_name'] or ''}}" class="layui-input" disabled>
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
            var id = $("input[name='id']").val();
            var index = parent.layer.getFrameIndex(window.name);


        });
    </script>
@endsection
@extends('common.edit')
