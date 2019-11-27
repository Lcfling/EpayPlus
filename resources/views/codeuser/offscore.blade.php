@section('title', '下分')
@section('content')
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">账号：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['account'] or ''}}" disabled class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">分数：</label>
        <div class="layui-input-inline">
            <input type="number"  name="score"  lay-verify="score" autocomplete="off" class="layui-input">
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
                score:function (value) {
                    if(value==null||value==''){
                        return '请输入分数';
                    }
                    var reg = new RegExp("^\\d{1,}$");
                    if(!reg.test(value)){
                        return '请输入正确分数';
                    }
                },
            });

            form.on('submit(formDemo)', function(data) {
                $.ajax({
                    url:"{{url('/admin/codeoffscore')}}",
                    data:$('form').serialize(),
                    type:'post',
                    dataType:'json',
                    success:function(res){
                        if(res.status == 1){
                            layer.msg(res.msg,{icon:6},function () {
                                parent.layer.close(index);
                                parent.location.reload();
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
