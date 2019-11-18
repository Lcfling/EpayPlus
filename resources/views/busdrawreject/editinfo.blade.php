@section('title', '提现信息')
@section('content')
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">商户ID：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['business_code']}}" class="layui-input" disabled >
        </div>
    </div>


    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">提现单号：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['order_sn']}}" class="layui-input" disabled>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">提现额度：</label>
        <div class="layui-input-inline">
            <input type="number" value="{{$info['money']/100}}" name="money" placeholder="请输入金额" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">开户人：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['name']}}" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">卡号：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['deposit_card']}}" id="sk_banknum" name="deposit_card" required lay-verify="sk_banknum" placeholder="请输入卡号" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">开户行：</label>
        <div class="layui-input-inline">
            <input type="text" value="{{$info['deposit_name']}}" id="sk_bankname" name="deposit_name" required placeholder="请输入开户行" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" style="width: 100px">申请时间：</label>
        <div class="layui-input-inline">
            <input type="text"  value="{{$info['creatime']}}" class="layui-input" disabled>
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
            var banklist={!! $banklist!!};//不转义字符
            $("#sk_banknum").blur(function(){
                var value=$(this).val();
                $.post("https://ccdcapi.alipay.com/validateAndCacheCardInfo.json",{cardNo:value,cardBinCheck:'true'},function(res){
                    //console.log(res); //不清楚返回值的打印出来看
                    //{"cardType":"DC","bank":"ICBC","key":"622200****412565805","messages":[],"validated":true,"stat":"ok"}
                    if(res.validated){
                        var name=banklist[res.bank];
                        //console.log(name);
                        $('#sk_bankname').val(name);
                        $('#sk_bankname').text(name);
                    }else{
                        layer.msg('银行卡号错误',{icon:5});
                        //setTimeout($("#deposit_card").focus(),1000); //获取焦点
                        $('#sk_bankname').val('');
                        $('#sk_bankname').text('');
                        return false;
                    }
                },'json');
            });
            form.on('submit(formDemo)', function(data) {
                $.ajax({
                    url:"{{url('/admin/busdrawreject_save')}}",
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
