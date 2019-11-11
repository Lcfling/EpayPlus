@section('title', '更改费率')
@section('content')
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">账号：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['account'] or ''}}" disabled class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">微信费率：</label>
        <div class="layui-input-inline">
            <input type="number"  value="{{$info['rate'] or ''}}" name="rate"  lay-verify="fee" placeholder="请输入微信费率(4位小数)" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">支付宝费率：</label>
        <div class="layui-input-inline">
            <input type="number"  value="{{$info['rates'] or ''}}" name="rates"  lay-verify="fee" placeholder="请输入支付宝费率(4位小数)" autocomplete="off" class="layui-input">
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
                fee:function (value) {
                    if(value==null||value==''){
                        return '请输入费率';
                    }
                    var reg = new RegExp("^[0-9]+(.?[0-9]{1,4})?$");
                    if(!reg.test(value)){
                        return '请输入正确费率';
                    }
                },
            });
            form.on('submit(formDemo)', function(data) {

                $.ajax({
                    url:"{{url('/admin/codeuserfee')}}",
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
