@section('title', '充值信息')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-normal addBtn" data-desc="添加充值信息" data-url="{{url('/admin/recharge/0/edit')}}"><i class="layui-icon">&#xe654;</i></button>
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['sk_name'] or '' }}" name="sk_name" placeholder="收款人" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['creatime'] or '' }}" name="creatime" placeholder="添加时间" onclick="layui.laydate({elem: this, festival: true})" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo1">搜索</button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-even lay-skin="nob">
        <input type="hidden" id="token" value="{{csrf_token()}}">
        <colgroup>
            <col class="hidden-xs" width="50">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="200">
            <col width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">序号</th>
            <th class="hidden-xs">收款姓名</th>
            <th class="hidden-xs">收款银行</th>
            <th class="hidden-xs">收款卡号</th>
            <th class="hidden-xs">状态</th>
            <th class="hidden-xs">添加时间</th>
            <th style="text-align: center">操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['sk_name']}}</td>
                <td class="hidden-xs">{{$info['sk_bankname']}}</td>
                <td>{{$info['sk_banknum']}}</td>
                <td class="hidden-xs">@if($info['status']==1)<span class="layui-btn layui-btn-small layui-btn-normal">已启用</span>@else<span class="layui-btn layui-btn-small layui-btn-warm">未启用</span>@endif</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td style="text-align: center">
                    <div class="layui-inline">
                        @if($info['status']==1)
                        <button class="layui-btn layui-btn-small layui-btn-danger del-btn" data-id="{{$info['id']}}" data-url="{{url('/admin/recharge/'.$info['id'])}}">删除</button>
                        @else
                        <button class="layui-btn layui-btn-small layui-btn-normal edits-btn" data-id="{{$info['id']}}" data-key="1" data-desc="启用">启用</button>
                        <button class="layui-btn layui-btn-small layui-btn-danger del-btn" data-id="{{$info['id']}}" data-url="{{url('/admin/recharge/'.$info['id'])}}">删除</button>
                        @endif
                    </div>
                </td>
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
        layui.use(['form', 'jquery','laydate', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer
            ;
            laydate({istoday: true});
            form.render();
            form.on('submit(formDemo)', function(data) {
            });
            $('.edits-btn').click(function () {
                var that = $(this);
                var id=$(this).attr('data-id');
                var status = that.attr('data-key');
                layer.confirm('确定要启用吗？',{title:'提示'},function () {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('#token').val()
                        },
                        url:"{{url('/admin/recharge/enable')}}",
                        data:{
                            "id":id,
                            "status":status
                        },
                        type:"post",
                        dataType:'json',
                        success:function (res) {
                            if(res.status==1){
                                layer.msg(res.msg,{icon:6});
                                location.reload();
                            }else{
                                layer.msg(res.msg,{shift: 6,icon:5});
                                location.reload();
                            }
                        }
                    });
                })
            });
        });
    </script>
@endsection
@extends('common.list')
