@section('title', '订单')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['user_id'] or '' }}" name="user_id" placeholder="请输入码商号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['business_code'] or '' }}" name="business_code" placeholder="请输入商户号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['order_sn'] or '' }}" name="order_sn" placeholder="请输入订单号" autocomplete="off" class="layui-input">
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
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">ID</th>
            <th class="hidden-xs">商户ID</th>
            <th class="hidden-xs">订单号</th>
            <th class="hidden-xs">码商ID</th>
            <th class="hidden-xs">码商收款</th>
            <th class="hidden-xs">支付类型</th>
            <th class="hidden-xs">支付金额</th>
            <th class="hidden-xs">实付金额</th>
            <th class="hidden-xs">支付状态</th>
            <th class="hidden-xs">回调状态</th>
            <th class="hidden-xs">手动回调</th>
            <th class="hidden-xs">创建时间</th>
            <th class="hidden-xs">平台订单号</th>
            <th class="hidden-xs">操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['business_code']}}</td>
                <td class="hidden-xs">{{$info['order_sn']}}</td>
                <td class="hidden-xs">{{$info['user_id']}}</td>
                <td class="hidden-xs">{{$info['sk_status']}}</td>
                <td class="hidden-xs">{{$info['payType']}}</td>
                <td class="hidden-xs">{{$info['payMoney']}}</td>
                <td class="hidden-xs">{{$info['tradeMoney']}}</td>
                <td class="hidden-xs">{{$info['status']}}</td>
                <td class="hidden-xs">{{$info['callback_status']}}</td>
                <td class="hidden-xs">{{$info['is_shoudong']}}</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td class="hidden-xs">{{$info['out_order_sn']}}</td>
                <td>
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-small layui-btn-normal edits-btn1" data-id="{{$info['id']}}" data-desc="审核通过"><i class="layui-icon">&#xe605;</i></button>
                        <button class="layui-btn layui-btn-small layui-btn-warm edits-btn2"  data-id="{{$info['id']}}" data-desc="驳回操作"><i class="layui-icon">&#x1006;</i></button>
                    </div>
                </td>
            </tr>
        @endforeach
        @if(!$list[0])
            <tr><td colspan="6" style="text-align: center;color: orangered;">暂无数据</td></tr>
        @endif
        </tbody>
        </tbody>
    </table>
    <div class="page-wrap">
        {{$list->render()}}
    </div>
@endsection
@section('js')
    <script>
        layui.use(['form', 'jquery','laydate', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer;

            form.render();
            form.on('submit(formDemo)', function(data) {
            });
        });
    </script>
@endsection
@extends('common.list')
