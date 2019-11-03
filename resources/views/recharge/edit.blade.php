@section('title', '角色编辑')
@section('content')
    <div class="layui-form-item">
        <label class="layui-form-label">收款姓名：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['sk_name'] or ''}}" name="sk_name" required lay-verify="sk_name" placeholder="请输入收款姓名" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">收款卡号：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['sk_banknum'] or ''}}" id="sk_banknum" name="sk_banknum" required lay-verify="sk_banknum" placeholder="请输入收款卡号" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">收款银行：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['sk_bankname'] or ''}}" id="sk_bankname" name="sk_bankname" required placeholder="请输入收款银行" autocomplete="off" class="layui-input">
        </div>
    </div>
@endsection
@section('id',$id)
@section('js')
    <script>
        layui.use(['form','jquery','laypage', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery;

            form.render();
            var layer = layui.layer;
            var banklist={!! $banklist!!};//不转义字符
            //console.log(banklist);
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
            form.verify({
                sk_banknum:[/^([1-9]{1})(\d{14}|\d{18})$/,'请填写正确的银行卡号'],
            });
            form.on('submit(formDemo)', function(data) {
                $.ajax({
                    url:"{{url('/admin/recharge')}}",
                    data:$('form').serialize(),
                    type:'post',
                    dataType:'json',
                    success:function(res){
                        if(res.status == 1){
                            layer.msg(res.msg,{icon:6});
                            var index = parent.layer.getFrameIndex(window.name);
                            setTimeout('parent.layer.close('+index+')',2000);
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
