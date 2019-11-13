@section('title', '二维码列表')
@section('header')
    <div class="layui-inline">
        <button class="layui-btn layui-btn-small layui-btn-normal addBtn" data-desc="添加客服" data-url="{{url('/admin/callcenter/0/edit')}}"><i class="layui-icon">&#xe654;</i></button>
        <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['user_id'] or '' }}" name="user_id" placeholder="请输入码商ID" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo1">搜索</button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-even lay-skin="nob">
        <colgroup>
            <col class="hidden-xs" width="50">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">ID</th>
            <th class="hidden-xs">码商ID</th>
            <th class="hidden-xs">姓名</th>
            <th class="hidden-xs">类型</th>
            <th class="hidden-xs">二维码</th>
            <th class="hidden-xs">是否删除</th>
            <th class="hidden-xs">状态</th>
            <th class="hidden-xs">创建时间</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['user_id']}}</td>
                <td class="hidden-xs">{{$info['name']}}</td>
                <td class="hidden-xs">@if($info['type']==1)微信@elseif($info['type']==2)支付宝@endif</td>

                <td>
                    <img src="{{$info['erweima']}}" width="50px" onclick="previewImg(this)">
                </td>
                <td class="hidden-xs">@if($info['status']==0)未删除@elseif($info['status']==1)已删除@endif</td>
                <td class="hidden-xs">@if($info['code_status']==0)开启@elseif($info['code_status']==1)关闭@endif</td>
                <td>{{$info['creatime']}}</td>

            </tr>
        @endforeach
        @if(!$list[0])
            <tr><td colspan="6" style="text-align: center;color: orangered;">暂无数据</td></tr>
        @endif
        </tbody>
    </table>
    <div class="page-wrap">
        {{$list->render()}}
    </div>
@endsection
@section('js')
    <script>
        layui.use(['form', 'jquery', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery,
                layer = layui.layer;

            form.render();
            form.on('submit(formDemo)', function(data) {
                console.log(data);
            });
            //layer.msg(layui.v);
        });

        function previewImg(obj) {
            var img = new Image();
            img.src=obj.src;
            var imgHtml = "<img src='" + obj.src + "' width='300px' height='300px'/>";
            //弹出层
            layer.open({
                type:1,
                shade:0.8,
                area:['300px','350px'],
                offset:'auto',
                shadeClose:true,
                scrollbar:false,
                title:"图片预览",
                content:imgHtml,
                cancel:function () {

                }
            });
        }
    </script>
@endsection
@extends('common.list')