@section('title', '用户列表')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-normal addBtn" data-desc="添加用户" data-url="{{url('/admin/users/0/edit')}}"><i class="layui-icon">&#xe654;</i></button>
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
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
            <col class="hidden-xs" width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">ID</th>
            <th class="hidden-xs">用户角色</th>
            <th class="hidden-xs">用户名</th>
            <th class="hidden-xs">邮箱</th>
            <th class="hidden-xs">手机号码</th>
            <th class="hidden-xs">登录状态</th>
            <th class="hidden-xs">操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['id']}}</td>
                <td class="hidden-xs">{{$info['roles'][0]['display_name'] or '已删除'}}</td>
                <td class="hidden-xs">{{$info['username']}}</td>
                <td class="hidden-xs">{{$info['email']}}</td>
                <td class="hidden-xs">{{$info['mobile']}}</td>
                <td class="hidden-xs">
                    <input type="checkbox" name="is_login" value="{{$info['id']}}" lay-skin="switch" lay-text="允许|禁止" lay-filter="is_login" {{ $info['is_login'] == 1 ? 'checked' : '' }}>
                </td>
                <td>
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-small layui-btn-normal edit-btn" data-id="{{$info['id']}}" data-desc="修改用户" data-url="{{url('/admin/users/'. $info['id'] .'/edit')}}"><i class="layui-icon">&#xe642;</i></button>
                        <button class="layui-btn layui-btn-small layui-btn-danger del-btn" data-id="{{$info['id']}}" data-url="{{url('/admin/users/'.$info['id'])}}"><i class="layui-icon">&#xe640;</i></button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <input type="hidden" id="token" value="{{csrf_token()}}">
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
                console.log(data);
            });
            //允许登录
            form.on('switch(is_login)', function(obj){
                //layer.tips(this.value + ' ' + this.name + '：'+ obj.elem.checked, obj.othis);
                var id=this.value,
                    status=obj.elem.checked;
                if(status==false){
                    var login=0;
                }else if(status==true){
                    login=1;
                }
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('#token').val()
                    },
                    url:"{{url('/admin/users_islogin')}}",
                    data:{
                        id:id,
                        login:login
                    },
                    type:'post',
                    dataType:'json',
                    success:function(res){
                        if(res.status == 1){
                            layer.msg(res.msg,{icon:6,time:1000},function () {
                                location.reload();
                            });

                        }else{
                            layer.msg(res.msg,{icon:5,time:1000});
                        }
                    },
                    error : function(XMLHttpRequest, textStatus, errorThrown) {
                        layer.msg('网络失败', {time: 1000});
                    }
                });
            });
        });
    </script>
@endsection
@extends('common.list')