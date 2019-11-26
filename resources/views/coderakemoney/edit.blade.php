@section('title', '佣金配置')
@section('content')
<div class="layui-form layui-form-pane">
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">激活佣金</label>
        <div class="layui-input-inline">
            <input type="num" value="{{$info['jhmoney']/100}}" id="jh" name="jhmoney"  placeholder="请填写激活佣金" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">1级返佣金额</label>
        <div class="layui-input-inline">
            <input type="num" value="{{$info['fymoney1']/100}}" id="fy1" name="fymoney1"  placeholder="请填写1级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">2级返佣金额</label>
        <div class="layui-input-inline">
            <input type="num" value="{{$info['fymoney2']/100}}" id="fy2" name="fymoney2"  placeholder="请填写2级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">3级返佣金额</label>
        <div class="layui-input-inline">
            <input type="num" value="{{$info['fymoney3']/100}}" id="fy3" name="fymoney3"  placeholder="请填写3级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">4级返佣金额</label>
        <div class="layui-input-inline">
            <input type="num" value="{{$info['fymoney4']/100}}" id="fy4" name="fymoney4"  placeholder="请填写4级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">5级返佣金额</label>
        <div class="layui-input-inline">
            <input type="num" value="{{$info['fymoney5']/100}}" id="fy5" name="fymoney5"  placeholder="请填写5级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">6级返佣金额</label>
        <div class="layui-input-inline">
            <input type="num" value="{{$info['fymoney6']/100}}" id="fy6" name="fymoney6"  placeholder="请填写6级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">7级返佣金额</label>
        <div class="layui-input-inline">
            <input type="num" value="{{$info['fymoney7']/100}}" id="fy7" name="fymoney7"  placeholder="请填写7级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">8级返佣金额</label>
        <div class="layui-input-inline">
            <input type="num" value="{{$info['fymoney8']/100}}" id="fy8" name="fymoney8"  placeholder="请填写8级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">9级返佣金额</label>
        <div class="layui-input-inline">
            <input type="num" value="{{$info['fymoney9']/100}}" id="fy9" name="fymoney9"  placeholder="请填写9级返佣金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 120px">10级返佣金额</label>
        <div class="layui-input-inline">
            <input type="num" value="{{$info['fymoney10']/100}}" id="fy10" name="fymoney10"  placeholder="请填写10级返佣金额" autocomplete="off" class="layui-input">
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
            var id = $("input[name='id']").val();
            var index = parent.layer.getFrameIndex(window.name);

            form.on('submit(formDemo)', function(data) {
                $.ajax({
                    url:"{{url('/admin/coderakemoneyUpdate')}}",
                    data:$('form').serialize(),
                    type:'post',
                    dataType:'json',
                    success:function(res){
                        if(res.status == 1){
                            layer.msg(res.msg,{icon:6,time:1000},function () {
                                parent.layer.close(index);
                                //window.parent.frames[1].location.reload();
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