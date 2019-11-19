@section('title', '订单管理')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['business_code'] or '' }}" name="business_code" placeholder="请输入商户号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['order_sn'] or '' }}" name="order_sn" placeholder="请输入平台订单号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['order_sn'] or '' }}" name="order_sn" placeholder="请输入商户订单号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['user_id'] or '' }}" name="user_id" placeholder="请输入码商号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <select name="status">
            <option value="">请选择支付状态</option>
            <option value="0" {{isset($input['status'])&&$input['status']==0?'selected':''}}>未支付</option>
            <option value="1" {{isset($input['status'])&&$input['status']==1?'selected':''}}>支付成功</option>
            <option value="2" {{isset($input['status'])&&$input['status']==2?'selected':''}}>过期</option>
            <option value="3" {{isset($input['status'])&&$input['status']==3?'selected':''}}>取消</option>
        </select>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['creatime'] or '' }}" name="creatime" placeholder="创建时间" onclick="layui.laydate({elem: this, festival: true})" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo">搜索</button>
        <button id="res" class="layui-btn layui-btn-primary">重置</button>
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-warm" name="excel" value="excel" lay-submit lay-filter="formDemo">导出EXCEL</button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-even lay-skin="nob">
        <colgroup>
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="250">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">序号</th>
            <th class="hidden-xs">商户ID</th>
            <th class="hidden-xs">平台订单号</th>
            <th class="hidden-xs">商户订单号</th>
            <th class="hidden-xs">码商ID</th>
            <th class="hidden-xs">二维码ID</th>
            <th class="hidden-xs">码商收款</th>
            <th class="hidden-xs">收款金额</th>
            <th class="hidden-xs">实际到账金额</th>
            <th class="hidden-xs">支付类型</th>
            <th class="hidden-xs">支付状态</th>
            <th class="hidden-xs">回调状态</th>
            <th class="hidden-xs">创建时间</th>
            <th class="hidden-xs">支付时间</th>
            <th class="hidden-xs" style="text-align: center">操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['business_code']}}</td>
                <td class="hidden-xs">{{$info['order_sn']}}</td>
                <td class="hidden-xs">{{$info['order_sn']}}</td>
                <td class="hidden-xs">{{$info['user_id']}}</td>
                <td class="hidden-xs">{{$info['erweima_id']}}</td>
                <td class="hidden-xs">
                    @if($info['sk_status']==0)<span class="layui-btn layui-btn-small layui-btn-danger">未收款</span>
                    @elseif($info['sk_status']==1)<span class="layui-btn layui-btn-small">手动收款</span>
                    @elseif($info['sk_status']==2)<span span class="layui-btn layui-btn-small layui-btn-warm">自动收款</span>
                    @endif</td>
                <td class="hidden-xs">{{$info['sk_money']/100}}</td>
                <td class="hidden-xs">{{$info['tradeMoney']/100}}</td>
                <td class="hidden-xs">
                    @if($info['payType']==0)<span class="layui-btn layui-btn-small layui-btn-primary">默认</span>
                    @elseif($info['payType']==1)<span class="layui-btn layui-btn-small">微信</span>
                    @elseif($info['payType']==2)<span class="layui-btn layui-btn-small layui-btn-normal">支付宝</span>
                    @endif</td>
                <td class="hidden-xs">
                    @if($info['status']==0)<span class="layui-btn layui-btn-small layui-btn-warm">未支付</span>
                    @elseif($info['status']==1)<span span class="layui-btn layui-btn-small layui-btn">支付成功</span>
                    @elseif($info['status']==2)<span class="layui-btn layui-btn-small layui-btn-normal">过期</span>
                    @elseif($info['status']==3)<span class="layui-btn layui-btn-small layui-btn-danger">取消</span>
                    @endif</td>
                <td class="hidden-xs">
                    @if($info['callback_status']==0)<span class="layui-btn layui-btn-small layui-btn-primary">未处理</span>
                    @elseif($info['callback_status']==1)<span span class="layui-btn layui-btn-small layui-btn">推送成功</span>
                    @elseif($info['callback_status']==2)<span class="layui-btn layui-btn-small layui-btn-danger">推送失败</span>@endif</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td class="hidden-xs">@if($info['status']==1){{$info['paytime']}}@endif</td>
                <td style="text-align: center">
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-small layui-btn-normal edits-btn1" data-id="{{$info['order_sn']}}" data-desc="补单">补单</button>
                        <button class="layui-btn layui-btn-small layui-btn-warm edits-btn2"  data-id="{{$info['order_sn']}}" data-desc="超时补单">超时补单</button>
                        <button class="layui-btn layui-btn-small layui-btn-default edits-btn3"  data-id="{{$info['order_sn']}}" data-desc="手动回调">手动回调</button>
                    </div>
                </td>
            </tr>
        @endforeach
        @if(!$list[0])
            <tr><td colspan="6" style="text-align: center;color: orangered;">暂无数据</td></tr>
        @endif
        </tbody>
        <input type="hidden" id="token" value="{{csrf_token()}}">
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
            $('#res').click(function () {
                $("input[name='business_code']").val('');
                $("input[name='order_sn']").val('');
                $("input[name='user_id']").val('');
                $("input[name='creatime']").val('');
                $("select[name='status']").val('');
                $('form').submit();
            });
            //补单
            $('.edits-btn1').click(function () {
                var that = $(this);
                var order_sn=$(this).attr('data-id');
                //'console.log(id);
                layer.confirm('确定要补单吗？',{title:'提示'},function (index) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('#token').val()
                            },
                            url:"{{url('/admin/order/budan')}}",
                            data:{
                                "order_sn":order_sn,
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


            //超时补单
            $('.edits-btn2').click(function () {
                var that = $(this);
                var order_sn=$(this).attr('data-id');
                layer.confirm('确定要超时补单吗？',{title:'提示'},function (index) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('#token').val()
                            },
                            url:"{{url('/admin/order/csbudan')}}",
                            data:{
                                "order_sn":order_sn,
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

            //手动回调
            $('.edits-btn3').click(function () {
                var that = $(this);
                var order_sn=$(this).attr('data-id');
                layer.confirm('确定要手动回调吗？',{title:'提示'},function (index) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('#token').val()
                            },
                            url:"{{url('/admin/order/sfpushfirst')}}",
                            data:{
                                "order_sn":order_sn,
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
