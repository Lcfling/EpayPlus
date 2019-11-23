@section('title', '充值通过')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['user_id'] or '' }}" name="user_id" placeholder="码商ID" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['name'] or '' }}" name="name" placeholder="充值姓名" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['creatime'] or '' }}" name="creatime" placeholder="申请时间" onclick="layui.laydate({elem: this, festival: true,min:'{{$min}}'})" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo">搜索</button>
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
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">序号</th>
            <th class="hidden-xs">码商ID</th>
            <th class="hidden-xs">姓名</th>
            <th class="hidden-xs">金额</th>
            <th class="hidden-xs">充值凭证</th>
            <th class="hidden-xs">收款姓名</th>
            <th class="hidden-xs">收款卡号</th>
            <th class="hidden-xs">收款银行</th>
            <th class="hidden-xs">充值状态</th>
            <th class="hidden-xs">申请时间</th>
            <th style="text-align: center">操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['user_id']}}</td>
                <td class="hidden-xs">{{$info['name']}}</td>
                <td class="hidden-xs">{{$info['score']/100}}</td>
                <td>
                    <img src="{{$info['czimg']}}" width="50px" onclick="previewImg(this)">
                </td>
                <td class="hidden-xs">{{$info['sk_name']}}</td>
                <td class="hidden-xs">{{$info['sk_banknum']}}</td>
                <td class="hidden-xs">{{$info['sk_bankname']}}</td>
                <td class="hidden-xs"><span class="layui-btn layui-btn-small layui-btn-warm">待审核</span></td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td style="text-align: center">
                    <div class="layui-inline">
                        @if($info['status']==0)
                            <button class="layui-btn layui-btn-small layui-btn-normal edits-btn1" data-id="{{$info['id']}}"  data-desc="通过">通过</button>
                            <button class="layui-btn layui-btn-small layui-btn-danger edits-btn2" data-id="{{$info['id']}}"  data-desc="驳回">驳回</button>
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
                layer = layui.layer;

            laydate({istoday: true});
            form.render();
            form.on('submit(formDemo)', function(data) {
            });

            $('.edits-btn1').click(function () {
                var that = $(this);
                var id=$(this).attr('data-id');
                layer.confirm('确定要通过吗?',{title:'提示'},function () {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('#token').val()
                        },
                        url:"{{url('/admin/rechargelist/pass')}}",
                        data:{
                            "id":id,
                            "status":status
                        },
                        type:"post",
                        dataType:'json',
                        success:function (res) {
                            if(res.status == 1){
                                layer.msg(res.msg,{icon:6,time:1000},function () {
                                    location.reload();
                                });

                            }else{
                                layer.msg(res.msg,{icon:5,time:1000});
                            }
                        }
                    });
                })
            });
            //驳回
            $('.edits-btn2').click(function () {
                var that = $(this);
                var id=$(this).attr('data-id');
                layer.confirm('确定要驳回吗?',{title:'提示'},function () {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('#token').val()
                        },
                        url:"{{url('/admin/rechargelist/reject')}}",
                        data:{
                            "id":id,
                            "status":status
                        },
                        type:"post",
                        dataType:'json',
                        success:function (res) {
                            if(res.status == 1){
                                layer.msg(res.msg,{icon:6,time:1000},function () {
                                    location.reload();
                                });

                            }else{
                                layer.msg(res.msg,{icon:5,time:1000});
                            }
                        }
                    });
                })
            });

        });
        function previewImg(obj) {
            var img = new Image();
            img.src=obj.src;
            var imgHtml = "<img src='" + obj.src + "' width='400px' height='700px'/>";
            //弹出层
            layer.open({
                type:1,
                shade:0.8,
                offset:'auto',
                area:['400px','700px'],
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
