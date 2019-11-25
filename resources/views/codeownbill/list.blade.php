@section('title', '码商流水')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $own_id or '' }}" name="user_id"  class="layui-input" disabled>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['business_code'] or '' }}" name="business_code" placeholder="请输入商户标识" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['order_sn'] or '' }}" name="order_sn" placeholder="请输入订单号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['erweima_id'] or '' }}" name="erweima_id" placeholder="请输入二维码ID" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo">搜索</button>
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
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">序号</th>
            <th class="hidden-xs">码商ID</th>
            <th class="hidden-xs">商户标识</th>
            <th class="hidden-xs">订单号</th>
            <th class="hidden-xs">积分</th>
            <th class="hidden-xs">二维码ID</th>
            <th class="hidden-xs">状态</th>
            <th class="hidden-xs">支付类型</th>
            <th class="hidden-xs">创建时间</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['user_id']}}</td>
                <td class="hidden-xs">{{$info['business_code']}}</td>
                <td class="hidden-xs">{{$info['order_sn']}}</td>
                <td class="hidden-xs">{{$info['score']/100}}</td>
                <td class="hidden-xs">{{$info['erweima_id']}}</td>
                <td class="hidden-xs">
                    @if($info['status']==1)<span class="layui-btn layui-btn-small layui-btn-primary">充值</span>
                    @elseif($info['status']==2)<span class="layui-btn layui-btn-small layui-btn">第三方支付</span>
                    @elseif($info['status']==3)<span class="layui-btn layui-btn-small layui-btn-disabled">冻结</span>
                    @elseif($info['status']==4)<span class="layui-btn layui-btn-small layui-btn-warm">充值解冻</span>
                    @elseif($info['status']==5)<span class="layui-btn layui-btn-small layui-btn-normal">佣金</span>
                    @elseif($info['status']==6)<span class="layui-btn layui-btn-small layui-btn-danger">提现</span>
                    @endif
                </td>
                <td class="hidden-xs">
                    @if($info['payType']==0)<span class="layui-btn layui-btn-small layui-btn-primary">默认</span>
                    @elseif($info['payType']==1)<span class="layui-btn layui-btn-small">微信</span>
                    @elseif($info['payType']==2)<span class="layui-btn layui-btn-small layui-btn-normal">支付宝</span>
                    @endif</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
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
