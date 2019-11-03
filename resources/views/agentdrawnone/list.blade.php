@section('title', '代理商')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text" value="{{ $input['agent_id'] or '' }}" name="agent_id" placeholder="请输入代理商编号号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input class="layui-input" name="begin" placeholder="开始日期" onclick="layui.laydate({elem: this, festival: true})" value="{{ $input['creatime'] or '' }}">
    </div>
    <div class="layui-inline">
        <input class="layui-input" name="begin" placeholder="结束日期" onclick="layui.laydate({elem: this, festival: true})" value="{{ $input['endtime'] or '' }}">
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
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="200">
            <col width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">ID</th>
            <th class="hidden-xs">代理商编号</th>
            <th class="hidden-xs">申请时间</th>
            <th class="hidden-xs">提现额度</th>
            <th class="hidden-xs">开户人</th>
            <th class="hidden-xs">开户行</th>
            <th class="hidden-xs">卡号</th>
            <th class="hidden-xs">状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['agent_id']}}</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td class="hidden-xs">{{$info['money']}}</td>
                <td class="hidden-xs">{{$info['name']}}</td>
                <td class="hidden-xs">{{$info['deposit_name']}}</td>
                <td class="hidden-xs">{{$info['deposit_card']}}</td>
                <td class="hidden-xs">未结算</td>
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
            //通过
            $('.edits-btn1').click(function () {
                var that = $(this);
                var id=$(this).attr('data-id');
                layer.confirm('确定要通过吗？',{title:'提示'},function (index) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('#token').val()
                            },
                            url:"{{url('/admin/agentdrawnone/pass')}}",
                            data:{
                                "id":id,
                            },
                            type:"post",
                            dataType:"json",
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
                    }
                );
            });
            //驳回
            $('.edits-btn2').click(function () {
                var that = $(this);
                var id=$(this).attr('data-id');
                layer.confirm('确定要驳回吗？',{title:'提示'},function (index) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('#token').val()
                            },
                            url:"{{url('/admin/agentdrawnone/reject')}}",
                            data:{
                                "id":id,
                            },
                            type:"post",
                            dataType:"json",
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
                    }
                );
            });
        });
    </script>
@endsection
@extends('common.list')
