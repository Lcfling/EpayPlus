@section('title', '公告管理')
@section('header')
    <div class="layui-inline">
        <a class="layui-btn layui-btn-small layui-btn-normal" data-desc="添加公告" onclick="add()"><i class="layui-icon">&#xe654;</i></a>
        <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-even lay-skin="nob">
        <colgroup>
            <col class="hidden-xs" width="50">
            <col class="hidden-xs" width="80">
            <col>
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">ID</th>
            <th class="hidden-xs">标题</th>
            <th>内容</th>
            <th>创建时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($pager as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['title']}}</td>
                <td>{{$info['content']}}</td>
                <td>{{$info['creattime']}}</td>
                <td>
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-small layui-btn-normal edit-btn" data-id="{{$info['id']}}" data-desc="修改公告" data-url="{{url('/admin/notices/'. $info['id'] .'/edit')}}"><i class="layui-icon">&#xe642;</i></button>
                        <button class="layui-btn layui-btn-small layui-btn-danger del-btn" data-id="{{$info['id']}}" data-url="{{url('/admin/notices/'.$info['id'])}}"><i class="layui-icon">&#xe640;</i></button>
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

        function add() {
            layer.open({
                type: 2,
                title: '添加配置',
                closeBtn: 1,
                area: ['400px','400px'],
                shadeClose: false, //点击遮罩关闭
                resize:false,
                content: ['/admin/notices/0/edit','no'],
                end:function(){

                }
            });
        }

    </script>
@endsection
@extends('common.list')