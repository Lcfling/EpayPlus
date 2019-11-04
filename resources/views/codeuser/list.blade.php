@section('title', '码商')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-normal addBtn" data-desc="添加码商" data-url="{{url('/admin/codeuser/0/edit')}}"><i class="layui-icon">&#xe654;</i></button>
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['user_id'] or '' }}" name="user_id" placeholder="请输入码商号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo1">搜索</button>
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-warm" lay-submit name="excel" value="is" lay-filter="formDemo2">导出Excel</button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-even lay-skin="nob">
        <colgroup>
            <col class="hidden-xs" width="50">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="200">
            <col width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">ID</th>
            <th class="hidden-xs">账号</th>
            <th class="hidden-xs">联系电话</th>
            <th>上级</th>
            <th class="hidden-xs">身份</th>
            <th class="hidden-xs">派单总数</th>
            <th class="hidden-xs">派单总额</th>
            <th class="hidden-xs">成单总数</th>
            <th class="hidden-xs">成单总额</th>
            <th class="hidden-xs">剩余分数</th>
            <th class="hidden-xs">微信费率</th>
            <th class="hidden-xs">支付宝费率</th>
            <th class="hidden-xs">接单状态</th>
            <th class="hidden-xs">账号状态</th>
            <th class="hidden-xs">佣金总额</th>
            <th class="hidden-xs">二维码个数</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($pager as $info)
            <tr>
                <td class="hidden-xs">{{$info['user_id']}}</td>
                <td class="hidden-xs">{{$info['account']}}</td>
                <td class="hidden-xs">{{$info['mobile']}}</td>
                <td class="hidden-xs">{{$info['pid']}}</td>
                <td class="hidden-xs">{{$info['shenfen']}}</td>
                <td class="hidden-xs"></td>
                <td class="hidden-xs"></td>
                <td class="hidden-xs"></td>
                <td class="hidden-xs"></td>
                <td class="hidden-xs"></td>
                <td class="hidden-xs">{{$info['rate']}}%</td>
                <td class="hidden-xs">{{$info['rates']}}%</td>
                <td class="hidden-xs">{{$info['take_status']}}</td>
                <td class="hidden-xs">{{$info['jh_status']}}</td>
                <td class="hidden-xs"></td>
                <td class="hidden-xs"></td>
                <td>
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-small layui-btn-normal edit-btn" data-id="{{$info['user_id']}}" data-desc="编辑码商" data-url="{{url('/admin/codeuser/'. $info['user_id'] .'/edit')}}"><i class="layui-icon">&#xe642;</i></button>
                        <button class="layui-btn layui-btn-small layui-btn-danger del-btn" data-id="{{$info['user_id']}}" data-url="{{url('/admin/codeuser/'.$info['user_id'])}}"><i class="layui-icon">&#xe640;</i></button>
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
        layui.use(['form', 'jquery','laydate', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer
            ;
            laydate({istoday: true});
            form.render();
            form.on('submit(formDemo1)', function(data) {
            });
            form.on('submit(formDemo2)', function(data) {
            });
        });
    </script>
@endsection
@extends('common.list')
