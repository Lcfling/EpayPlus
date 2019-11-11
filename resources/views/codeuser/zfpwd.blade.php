@section('title', '登录密码')
@section('content')

    <div class="layui-form-item">
        <label class="layui-form-label">账号：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['account'] or ''}}" disabled class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">登录密码：</label>
        <div class="layui-input-block">
            <input type="password"  name="zf_pwd" required placeholder="请输入支付密码(6位纯数字)" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">确认密码：</label>
        <div class="layui-input-block">
            <input type="password" required lay-verify="confirmPass" placeholder="请确认登录密码" autocomplete="off" class="layui-input">
        </div>
    </div>

@endsection
@section('id',$id)
@section('js')
    <script>
        layui.use(['form','jquery','laypage', 'layer'], function() {
            var form = layui.form(),
                layer = layui.layer,
                $ = layui.jquery;
            form.render();
            var id = $("input[name='id']").val();
            var index = parent.layer.getFrameIndex(window.name);

            form.verify({
                confirmPass:function(value){
                    if($('input[name=zf_pwd]').val() !== value)
                        return '两次密码输入不一致！';
                    var reg1 = new RegExp("^\\d{6}$");
                    if(!reg1.test(value)){
                        return '密码为6位纯数字';
                    }
                },
            });

            form.on('submit(formDemo)', function(data) {
                $.ajax({
                    url:"{{url('/admin/codenewpaypwd')}}",
                    data:$('form').serialize(),
                    type:'post',
                    dataType:'json',
                    success:function(res){
                        if(res.status == 1){
                            layer.msg(res.msg,{icon:6},function () {
                                parent.layer.close(index);
                                window.parent.frames[1].location.reload();
                            });

                        }else{
                            layer.msg(res.msg,{shift: 6,icon:5});
                        }
                    },
                    error : function(XMLHttpRequest, textStatus, errorThrown) {
                        layer.msg('网络失败', {time: 1000});
                    }
                });
                return false;
            });

        });
    </script>
@endsection
@extends('common.edit')
