@section('title', '客服编辑')
@section('content')

    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">客服昵称：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['content'] or ''}}" id="name" name="content" placeholder="请填写标题" lay-verify="required" lay-reqText="请填写标题" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">二维码上传：</label>
        <div class="layui-input-inline">
            <input type="file" id="url" name="url">
            <img src="{{$info['url'] or ''}}" style="width:200px;height:200px;">
            <input type="hidden" id="token" value="{{csrf_token()}}">
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

            form.on('submit(formDemo)', function(data) {
                var formData = new FormData();
                formData.append('url',$('#url').prop('files')[0]);
                formData.append('content',$('#name').val());
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('#token').val(),
                    }
                });
                if(id==0){
                    $.ajax({
                        url:'{{url('/admin/callcenter')}}',
                        data:formData,
                        type:'post',
                        dataType:'json',
                        contentType: false,
                        processData: false,
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
                }else{
                    formData.append('id',id);
                    $.ajax({
                        url:"{{url('/admin/callcenterUpdate')}}",
                        data:formData,
                        type:'post',
                        dataType:'json',
                        contentType: false,
                        processData: false,
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
                }
                return false;
            });
        });
    </script>
@endsection
@extends('common.edit')