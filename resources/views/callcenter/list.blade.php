@section('title', '客服列表')
@section('header')
    <div class="layui-inline">
        <button class="layui-btn layui-btn-small layui-btn-normal addBtn" data-desc="添加客服" data-url="{{url('/admin/callcenter/0/edit')}}"><i class="layui-icon">&#xe654;</i></button>
        <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['content'] or '' }}" name="content" placeholder="请输入客服昵称" autocomplete="off" class="layui-input">
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
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">ID</th>
            <th class="hidden-xs">名字</th>
            <th>客服二维码</th>
            <th>创建时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($pager as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['content']}}</td>
                <td>
                    <img src="{{$info['url']}}" width="50px" onclick="previewImg(this)">
                </td>
                <td>{{$info['creatime']}}</td>
                <td>
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-small layui-btn-normal edit-btn" data-id="{{$info['id']}}" data-desc="修改客服" data-url="{{url('/admin/callcenter/'. $info['id'] .'/edit')}}"><i class="layui-icon">&#xe642;</i></button>
                        <button class="layui-btn layui-btn-small layui-btn-danger del-btn" data-id="{{$info['id']}}" data-url="{{url('/admin/callcenter/'.$info['id'])}}"><i class="layui-icon">&#xe640;</i></button>
                    </div>
                </td>
            </tr>
        @endforeach
        @if(!$pager[0])
            <tr><td colspan="6" style="text-align: center;color: orangered;">暂无数据</td></tr>
        @endif
        </tbody>
    </table>
    <div class="page-wrap">
        {{$pager->render()}}
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