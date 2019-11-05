@section('title', '商户')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-normal addBtn" data-desc="添加商户" data-url="{{url('/admin/business/0/edit')}}"><i class="layui-icon">&#xe654;</i></button>
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['business_code'] or '' }}" name="business_code" placeholder="请输入商户ID" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['account'] or '' }}" name="account" placeholder="请输入商户账号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['nickname'] or '' }}" name="nickname" placeholder="请输入商户昵称" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo1">搜索</button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-even lay-skin="nob">
        <colgroup>
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="250">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">ID</th>
            <th class="hidden-xs">帐号</th>
            <th class="hidden-xs">商户昵称</th>
            <th class="hidden-xs">联系电话</th>
            <th class="hidden-xs">身份</th>
            <th class="hidden-xs">费率</th>
            <th class="hidden-xs">状态</th>
            <th class="hidden-xs">创建时间</th>
            <th class="hidden-xs">更新时间</th>
            <th class="hidden-xs">操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['business_code']}}</td>
                <td class="hidden-xs">{{$info['account']}}</td>
                <td class="hidden-xs">{{$info['nickname']}}</td>
                <td class="hidden-xs">{{$info['mobile']}}</td>
                <td class="hidden-xs">{{$info['shenfen']}}</td>
                <td class="hidden-xs">{{$info['fee']}}%</td>
                <td class="hidden-xs">{{$info['status']}}</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td class="hidden-xs">{{$info['updatetime']}}</td>
                <td>
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-small layui-btn-normal edit-btn" data-id="{{$info['business_code']}}" data-desc="编辑商户" data-url="{{url('/admin/business/'. $info['business_code'] .'/edit')}}">编辑</button>
                        <a class="layui-btn layui-btn-small layui-btn-danger" onclick="editpwd({{$info['business_code']}})">登录密码</a>
                        <a class="layui-btn layui-btn-small layui-btn-warm" onclick="editpayword({{$info['business_code']}})">支付密码</a>
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
        function editpwd(id) {
            var id=id;
            layer.open({
                type: 2,
                title: '修改登录密码',
                closeBtn: 1,
                area: ['500px','500px'],
                shadeClose: false, //点击遮罩关闭
                resize:false,
                content: ['/admin/business/buspwd/'+id,'no'],
                end:function(){

                }
            });
        }
        function editpayword(id) {
            var id=id;
            layer.open({
                type: 2,
                title: '修改支付密码',
                closeBtn: 1,
                area: ['500px','500px'],
                shadeClose: false, //点击遮罩关闭
                resize:false,
                content: ['/admin/business/buspayword/'+id,'no'],
                end:function(){

                }
            });
        }
    </script>
@endsection
@extends('common.list')
