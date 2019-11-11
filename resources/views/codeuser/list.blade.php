@section('title', '码商')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-normal addBtn" data-desc="添加码商" data-url="{{url('/admin/codeuser/0/edit')}}"><i class="layui-icon">&#xe654;</i></button>
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text"  value="{{ $input['user_id'] or '' }}" name="user_id" placeholder="请输入码商号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo1">搜索</button>
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-warm" lay-submit name="excel" value="is" lay-filter="formDemo2">导出Excel</button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-even lay-skin="nob">
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
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="300">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">ID</th>
            <th class="hidden-xs">账号</th>
            <th class="hidden-xs">联系电话</th>
            <th class="hidden-xs">上级</th>
            <th class="hidden-xs">身份</th>
            <th class="hidden-xs">剩余分数</th>
            <th class="hidden-xs">微信费率</th>
            <th class="hidden-xs">支付宝费率</th>
            <th class="hidden-xs">接单状态</th>
            <th class="hidden-xs">账号状态</th>
            <th class="hidden-xs">佣金总额</th>
            <th class="hidden-xs">二维码个数</th>
            <th class="hidden-xs">状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($pager as $info)
            <tr>
                <td class="hidden-xs">{{$info['user_id']}}</td>
                <td class="hidden-xs">{{$info['account']}}</td>
                <td class="hidden-xs">{{$info['mobile']}}</td>
                <td class="hidden-xs">{{$info['pid']}}</td>
                <td class="hidden-xs">{{$info['shenfen']}}</td>
                <td class="hidden-xs"></td>
                <td class="hidden-xs">{{$info['rate']}}%</td>
                <td class="hidden-xs">{{$info['rates']}}%</td>
                <td class="hidden-xs">@if($info['take_status']==0)未接单@elseif($info['take_status']==1)已接单@endif</td>
                <td class="hidden-xs">@if($info['jh_status']==0)未激活@elseif($info['take_status']==1)已激活@endif</td>
                <td class="hidden-xs"></td>
                <td class="hidden-xs">{{$info['imsi_num']}}</td>
                <td class="hidden-xs">
                    <input type="checkbox" name="status" value="{{$info['user_id']}}" lay-skin="switch" lay-text="开启|封禁" lay-filter="status" {{ $info['is_over'] == 0 ? 'checked' : '' }}>
                </td>
                <td>
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-small layui-btn-normal edit-btn" data-id="{{$info['user_id']}}" data-desc="编辑码商" data-url="{{url('/admin/codeuser/'. $info['user_id'] .'/edit')}}">编辑</button>
                        <a class="layui-btn layui-btn-small layui-btn" onclick="shangfen({{$info['user_id']}})">上分</a>
                        <a class="layui-btn layui-btn-small layui-btn-primary" onclick="xiafen({{$info['user_id']}})">下分</a>
                        <a class="layui-btn layui-btn-small layui-btn-worm" onclick="addqr({{$info['user_id']}})">加码</a>
                        <a class="layui-btn layui-btn-small layui-btn-primary" onclick="tomsg({{$info['user_id']}})">通知</a>
                        <a class="layui-btn layui-btn-small layui-btn-normal" href="{{url('admin/codeownbill/own',['id'=>$info['user_id']])}}">流水</a>
                        <a class="layui-btn layui-btn-small layui-btn-danger " onclick="ownfee({{$info['user_id']}})">费率</a>
                        <a class="layui-btn layui-btn-small layui-btn-danger" onclick="logpwd({{$info['user_id']}})">登录密码</a>
                        <a class="layui-btn layui-btn-small layui-btn-primary" onclick="secondpwd({{$info['user_id']}})">二级密码</a>
                        <a class="layui-btn layui-btn-small layui-btn-warm" onclick="zfpwd({{$info['user_id']}})">支付密码</a>
                    </div>
                </td>
            </tr>
        @endforeach
        @if(!$pager[0])
            <tr><td colspan="6" style="text-align: center;color: orangered;">暂无数据</td></tr>
        @endif
        </tbody>
        <input type="hidden" id="token" value="{{csrf_token()}}">
    </table>
    <div class="page-wrap">
        {{$pager->render()}}
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
            //封禁开关
            form.on('switch(status)', function(obj){
                layer.tips(this.value + ' ' + this.name + '：'+ obj.elem.checked, obj.othis);
                var id=this.value,
                    status=obj.elem.checked;
                if(status==false){
                    var is_over=1;
                }else if(status==true){
                    is_over=0;
                }
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('#token').val()
                    },
                    url:"{{url('/admin/codeuser_isover')}}",
                    data:{
                        id:id,
                        is_over:is_over
                    },
                    type:'post',
                    dataType:'json',
                    success:function(res){
                        if(res.status == 1){
                            layer.msg(res.msg,{icon:6},function () {
                                location.reload();
                            });

                        }else{
                            layer.msg(res.msg,{shift: 6,icon:5});
                        }
                    },
                    error : function(XMLHttpRequest, textStatus, errorThrown) {
                        layer.msg('网络失败', {time: 1000});
                    }
                });
            });
        });
        function addqr(id) {
            var id=id;
            layer.open({
                type: 2,
                title: '增加二维码',
                closeBtn: 1,
                area: ['500px','300px'],
                shadeClose: false, //点击遮罩关闭
                content: ['/admin/codeuser/addqr/'+id],
                end:function(){

                }
            });
        }
        function tomsg(id) {
            var id=id;
            layer.open({
                type: 2,
                title: '增加二维码数量',
                closeBtn: 1,
                area: ['500px','500px'],
                shadeClose: false, //点击遮罩关闭
                content: ['/admin/codeuser/tomsg/'+id],
                end:function(){

                }
            });
        }
        function ownfee(id) {
            var id=id;
            layer.open({
                type: 2,
                title: '更改费率',
                closeBtn: 1,
                area: ['500px','500px'],
                shadeClose: false, //点击遮罩关闭
                content: ['/admin/codeuser/ownfee/'+id],
                end:function(){

                }
            });
        }
        function logpwd(id) {
            var id=id;
            layer.open({
                type: 2,
                title: '更改登录密码',
                closeBtn: 1,
                area: ['500px','500px'],
                shadeClose: false, //点击遮罩关闭
                content: ['/admin/codeuser/logpwd/'+id],
                end:function(){

                }
            });
        }
        function secondpwd(id) {
            var id=id;
            layer.open({
                type: 2,
                title: '更改二级密码',
                closeBtn: 1,
                area: ['500px','500px'],
                shadeClose: false, //点击遮罩关闭
                content: ['/admin/codeuser/secondpwd/'+id],
                end:function(){

                }
            });
        }
        function zfpwd(id) {
            var id=id;
            layer.open({
                type: 2,
                title: '更改支付密码',
                closeBtn: 1,
                area: ['500px','500px'],
                shadeClose: false, //点击遮罩关闭
                content: ['/admin/codeuser/zfpwd/'+id],
                end:function(){

                }
            });
        }
    </script>
@endsection
@extends('common.list')
