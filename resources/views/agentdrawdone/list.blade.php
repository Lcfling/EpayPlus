@section('title', '代理商')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text" value="{{ $input['agent_id'] or '' }}" name="agent_id" placeholder="请输入代理商ID" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text" value="{{ $input['order_sn'] or '' }}" name="order_sn" placeholder="请输入提现单号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input class="layui-input" name="creatime" placeholder="申请日期" onclick="layui.laydate({elem: this, festival: true})" value="{{ $input['creatime'] or '' }}" autocomplete="off">
    </div>
    <div class="layui-inline">
        <input class="layui-input" name="endtime" placeholder="审批日期" onclick="layui.laydate({elem: this, festival: true})" value="{{ $input['endtime'] or '' }}" autocomplete="off">
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
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="100">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">序号</th>
            <th class="hidden-xs">代理商ID</th>
            <th class="hidden-xs">提现单号</th>
            <th class="hidden-xs">提现额度</th>
            <th class="hidden-xs">开户人</th>
            <th class="hidden-xs">开户行</th>
            <th class="hidden-xs">卡号</th>
            <th class="hidden-xs">申请时间</th>
            <th class="hidden-xs">审批时间</th>
            <th class="hidden-xs">状态</th>

        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['agent_id']}}</td>
                <td class="hidden-xs">{{$info['order_sn']}}</td>
                <td class="hidden-xs">{{$info['money']/100}}</td>
                <td class="hidden-xs">{{$info['name']}}</td>
                <td class="hidden-xs">{{$info['deposit_name']}}</td>
                <td class="hidden-xs">{{$info['deposit_card']}}</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td class="hidden-xs">{{$info['endtime']}}</td>
                <td class="hidden-xs"><span class="layui-btn layui-btn-small layui-btn-warm">已打款</span></td>
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
