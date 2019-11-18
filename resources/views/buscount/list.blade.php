@section('title', '商户账单')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['business_code'] or '' }}" name="business_code" placeholder="请输入商户号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['creatime'] or '' }}" name="creatime" placeholder="创建时间" onclick="layui.laydate({elem: this, festival: true})" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['savetime'] or '' }}" name="savetime" placeholder="更新时间" onclick="layui.laydate({elem: this, festival: true})" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo">搜索</button>
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-warm" name="excel" value="excel" lay-submit lay-filter="formDemo">导出EXCEL</button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-even lay-skin="nob">
        <colgroup>
            <col class="hidden-xs" width="50">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">序号</th>
            <th class="hidden-xs">商户ID</th>
            <th class="hidden-xs">余额</th>
            <th class="hidden-xs">总分</th>
            <th class="hidden-xs">创建时间</th>
            <th class="hidden-xs">更新时间</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['business_code']}}</td>
                <td class="hidden-xs">{{$info['balance']/100}}</td>
                <td class="hidden-xs">{{$info['tol_sore']/100}}</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td class="hidden-xs">{{$info['savetime']}}</td>
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
