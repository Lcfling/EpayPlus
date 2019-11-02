@section('title', '佣金配置')
@section('content')
<div class="layui-form layui-form-pane">
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">激活佣金</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['jhmoney'] or ''}}" id="jh" name="jhmoney"  placeholder="请填写激活佣金" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">1级返佣金额</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['fymoney1'] or ''}}" id="fy1" name="fymoney1"  placeholder="请填写1级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">2级返佣金额</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['fymoney2'] or ''}}" id="fy2" name="fymoney2"  placeholder="请填写2级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">3级返佣金额</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['fymoney3'] or ''}}" id="fy3" name="fymoney3"  placeholder="请填写3级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">4级返佣金额</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['fymoney4'] or ''}}" id="fy4" name="fymoney4"  placeholder="请填写4级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">5级返佣金额</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['fymoney5'] or ''}}" id="fy5" name="fymoney5"  placeholder="请填写5级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">6级返佣金额</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['fymoney6'] or ''}}" id="fy6" name="fymoney6"  placeholder="请填写6级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">7级返佣金额</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['fymoney7'] or ''}}" id="fy7" name="fymoney7"  placeholder="请填写7级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">8级返佣金额</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['fymoney8'] or ''}}" id="fy8" name="fymoney8"  placeholder="请填写8级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">9级返佣金额</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['fymoney9'] or ''}}" id="fy9" name="fymoney9"  placeholder="请填写9级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">10级返佣金额</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['fymoney10'] or ''}}" id="fy10" name="fymoney10"  placeholder="请填写10级返佣金额" autocomplete="off" class="layui-input">
        </div>
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
            var index = parent.layer.getFrameIndex(window.name);
            var jh=$("#jh").val(),
                fy1=$("#fy1").val(),
                fy2=$("#fy2").val(),
                fy3=$("#fy3").val(),
                fy4=$("#fy4").val(),
                fy5=$("#fy5").val(),
                fy6=$("#fy6").val(),
                fy7=$("#fy7").val(),
                fy8=$("#fy8").val(),
                fy9=$("#fy9").val(),
                fy10=$("#fy10").val();

            form.on('submit(formDemo)', function(data) {

                $.ajax({
                    url:"{{url('/admin/coderakemoneyUpdate')}}",
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