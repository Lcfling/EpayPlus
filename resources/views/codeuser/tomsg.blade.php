@section('title', '增加二维码')
@section('content')
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">账号：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['account'] or ''}}" disabled class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">标题：</label>
        <div class="layui-input-inline">
            <input type="text" name="title"  placeholder="请填写标题" lay-verify="required" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">内容：</label>
        <div class="layui-input-inline">
            <textarea name="content" placeholder="请填写内容"  lay-verify="required" style="width: 260px;height: 160px;resize: none"></textarea>
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

            form.on('submit(formDemo)', function(data) {
                //console.log($('form').serialize());
                $.ajax({
                    url:"{{url('/admin/codeputmsg')}}",
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
