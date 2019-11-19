@section('title', '更改费率')
@section('content')
    <div class="layui-form-item">
        <label class="layui-form-label">商户：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['nickname'] or ''}}" disabled class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">账号：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['account'] or ''}}" disabled class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">商户费率：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['fee'] or ''}}" name="fee" lay-verify="fee" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">一级代理：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$fee['agent1_id'] or ''}}" name="agent1_id"    placeholder="请输入一级代理ID" autocomplete="off" class="layui-input">
        </div>
        <label class="layui-form-label">一级费率：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$fee['agent1_fee'] or ''}}" name="agent1_fee"  placeholder="请输入一级费率" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">二级代理：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$fee['agent2_id'] or ''}}" name="agent2_id"   placeholder="请输入二级代理ID" autocomplete="off" class="layui-input">
        </div>
        <label class="layui-form-label">二级费率：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$fee['agent2_fee'] or ''}}" name="agent2_fee"  placeholder="请输入二级费率" autocomplete="off" class="layui-input">
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
                    var reg = new RegExp("^[0-9]+(.?[0-9]{1,2})?$");
                    if(!reg.test(value)){
                        return '请输入正确费率';
                    }
                },
            });

            form.on('submit(formDemo)', function(data) {

                $.ajax({
                    url:"{{url('/admin/busnewfee')}}",
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
