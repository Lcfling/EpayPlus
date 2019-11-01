@section('title', '公告编辑')
@section('content')

    <div class="layui-form-item">
        <label class="layui-form-label">标题：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['title'] or ''}}" name="title"  placeholder="请填写标题" lay-verify="required" lay-reqText="请填写标题" autocomplete="off" class="layui-input">
        </div>
    </div>


    <div class="layui-form-item">
        <label class="layui-form-label">内容：</label>
        <div class="layui-input-inline">
            <textarea name="content" placeholder="请填写内容" lay-verify="required" style="width: 260px;height: 160px;resize: none">{{$info['content'] or ''}}</textarea>
        </div>
    </div>


@endsection
@section('id',$id)
@section('js')
    <script>
        layui.use(['form','jquery','layer'], function() {
            var form = layui.form()
                ,layer = layui.layer
                ,$ = layui.jquery;
            form.render();
            var id = $("input[name='id']").val();
            var index = parent.layer.getFrameIndex(window.name);
            if(id==0){
                form.on('submit(formDemo)', function(data) {
                    $.ajax({
                        url:"{{url('/admin/notices')}}",
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
            }else{
                form.on('submit(formDemo)', function(data) {

                    $.ajax({
                        url:"{{url('/admin/noticesUpdate')}}",
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
            }

        });
    </script>
@endsection
@extends('common.edit')