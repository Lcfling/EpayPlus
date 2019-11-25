@section('title', '商户提现')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text" lay-verify="business_code" value="{{ $input['business_code'] or '' }}" name="business_code" placeholder="请输入商户标识" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text" value="{{ $input['order_sn'] or '' }}" name="order_sn" placeholder="请输入提现订单号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input class="layui-input" name="creatime" placeholder="申请日期" onclick="layui.laydate({elem: this, festival: true,min:'{{$min}}'})" value="{{ $input['creatime'] or '' }}" autocomplete="off">
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
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">序号</th>
            <th class="hidden-xs">商户标识</th>
            <th class="hidden-xs">提现订单号</th>
            <th class="hidden-xs">提现金额</th>
            <th class="hidden-xs">到账金额</th>
            <th class="hidden-xs">手续费</th>
            <th class="hidden-xs">开户人</th>
            <th class="hidden-xs">开户行</th>
            <th class="hidden-xs">卡号</th>
            <th class="hidden-xs">申请时间</th>
            <th class="hidden-xs">状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['business_code']}}</td>
                <td class="hidden-xs">{{$info['order_sn']}}</td>
                <td class="hidden-xs">{{$info['money']/100}}</td>
                <td class="hidden-xs">{{$info['tradeMoney']/100}}</td>
                <td class="hidden-xs">{{$info['feemoney']/100}}</td>
                <td class="hidden-xs">{{$info['name']}}</td>
                <td class="hidden-xs">{{$info['deposit_name']}}</td>
                <td class="hidden-xs">{{$info['deposit_card']}}</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td class="hidden-xs"><span class="layui-btn layui-btn-small layui-btn">未处理</span></td>
                <td>
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-small layui-btn-normal edits-btn1" data-id="{{$info['id']}}" data-desc="确认打款">确认打款</button>
                        <a class="layui-btn layui-btn-small layui-btn-warm"  onclick="bohui({{$info['id']}})">驳回</a>
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
                layer.confirm('确定要打款吗？',{title:'提示'},function (index) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('#token').val()
                            },
                            url:"{{url('/admin/busdrawnone/pass')}}",
                            data:{
                                "id":id,
                            },
                            type:"post",
                            dataType:"json",
                            success:function (res) {
                                if(res.status==1){
                                    layer.msg(res.msg,{icon:6,time:1000},function () {
                                        location.reload();
                                    });
                                    
                                }else{
                                    layer.msg(res.msg,{icon:5,time:1000},function () {
                                        location.reload();
                                    });
                                }
                            }
                        });
                    }
                );
            });
        });
        function bohui(id) {
            var id=id;
            layer.open({
                type: 2,
                title: '提现驳回',
                closeBtn: 1,
                area: ['500px','700px'],
                shadeClose: false, //点击遮罩关闭
                content: ['/admin/busdrawnone/bohui/'+id],
                end:function(){

                }
            });
        }

    </script>
@endsection
@extends('common.list')
