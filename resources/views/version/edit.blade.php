@section('title', '公告编辑')
@section('content')

    <div class="layui-form-item">
        <label class="layui-form-label">版本号：</label>
        <div class="layui-input-inline">
            <input type="text" name="version_no" value="{{$info['version_no'] or ''}}"   placeholder="请输入版本号" lay-verify="required" autocomplete="off" class="layui-input">
        </div>
    </div>


    <div class="layui-form-item">
        <label class="layui-form-label">更新内容：</label>
        <div class="layui-input-inline">
            <textarea name="detail" placeholder="请填写更新内容" lay-verify="required" style="width: 260px;height: 160px;resize: none">{{$info['detail'] or ''}}</textarea>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">更新类型：</label>
        <div class="layui-input-inline">
            <select name="force" lay-verify="required">
                <option value="">请选择类型</option>
                <option value="0" @if(isset($info['force'])&&$info['force']==0) selected @endif>非强制更新</option>
                <option value="1" @if(isset($info['force'])&&$info['force']==1) selected @endif>强制更新</option>
            </select>
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
                        url:"{{url('/admin/version')}}",
                        data:$('form').serialize(),
                        type:'post',
                        dataType:'json',
                        success:function(res){
                            if(res.status == 1){
                                layer.msg(res.msg,{icon:6,time:1000},function () {
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
                        url:"{{url('/admin/versionUpdate')}}",
                        data:$('form').serialize(),
                        type:'post',
                        dataType:'json',
                        success:function(res){
                            if(res.status == 1){
                                layer.msg(res.msg,{icon:6,time:1000},function () {
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