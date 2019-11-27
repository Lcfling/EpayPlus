@section('title', '商户账单')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['business_code'] or '' }}" name="business_code" placeholder="请输入商户标识" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input class="layui-input" name="creatime" placeholder="选择日期" onclick="layui.laydate({elem: this, festival: true,min:'{{$min}}'})" value="{{ $input['creatime'] or '' }}" autocomplete="off">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo">搜索</button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-even lay-skin="nob">
        <colgroup>
           
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
        </colgroup>
        <thead>
        <tr>
            
            <th class="hidden-xs">商户标识</th>
            <th class="hidden-xs">商户昵称</th>
            <th class="hidden-xs">商户电话</th>
            <th class="hidden-xs">商户费率</th>
            <th class="hidden-xs">成功率</th>
            <th class="hidden-xs">收款总额</th>
            <th class="hidden-xs">实收金额(扣除费率)</th>
            <th class="hidden-xs">提现总额</th>
            <th class="hidden-xs">提现实际到账总额</th>
            <th class="hidden-xs">收获盈利</th>            
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                
                <td class="hidden-xs">{{$info['business_code']}}</td>
                <td class="hidden-xs">{{$info['nickname']}}</td>
                <td class="hidden-xs">{{$info['mobile']}}</td>
                <td class="hidden-xs">{{$info['fee']*100}}%</td>
                <td class="hidden-xs">{{$info['done_rate']}}%</td>
                <td class="hidden-xs">{{$info['sk_money']}}</td>
                <td class="hidden-xs">{{$info['tradeMoney']}}</td>
                <td class="hidden-xs">{{$info['draw_money']}}</td>
                <td class="hidden-xs">{{$info['draw_tradeMoney']}}</td>
                <td class="hidden-xs">{{$info['profit']}}</td>
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
            laydate({istoday: true});
            form.render();
            form.on('submit(formDemo)', function(data) {
            });
        });
    </script>
@endsection
@extends('common.list')
