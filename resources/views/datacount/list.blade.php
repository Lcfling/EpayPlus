@section('title', '码商')
@section('header')
    <div class="layui-inline" style="width: 30%;margin-left: 30px">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>订单统计</legend>
            <br>
            <blockquote class="layui-elem-quote layui-text" style="veritical-align:middle;">
                <div class="layui-form-item">
                    <label style="font-size: 15px;">成功订单额</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 90px">11.00元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">成功资金额(扣除费率)</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 20px">11.00元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">成功总盈利</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 90px">11.00元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商总佣金</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 90px">11.00元</span>
                </div>
            </blockquote>
            <br>
        </fieldset>
    </div>

    <div class="layui-inline" style="width: 30%;margin-left: 30px">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>商户提现</legend>
            <br>
            <blockquote class="layui-elem-quote layui-text" style="veritical-align:middle;">
                <div class="layui-form-item">
                    <label style="font-size: 15px;">商户总提现</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 105px">{{$data['bus']['done']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">商户未提现</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 105px">{{$data['bus']['balance']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">商户提现中</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 105px">{{$data['bus']['none']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">商户提现总手续费</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 105px">{{$data['bus']['none']}}元</span>
                </div>
            </blockquote>
            <br>
        </fieldset>
    </div>

    <div class="layui-inline" style="width: 30%;margin-left: 30px">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>代理提现</legend>
            <br>
            <blockquote class="layui-elem-quote layui-text" style="veritical-align:middle;">
                <div class="layui-form-item">
                    <label style="font-size: 15px;">代理总提现</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 90px">{{$data['agent']['done']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">代理未提现</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 90px">{{$data['agent']['balance']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">代理提现中</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 90px">{{$data['agent']['none']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">代理提现总手续费</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 105px">{{$data['bus']['none']}}元</span>
                </div>
            </blockquote>
            <br>
        </fieldset>
    </div>

    <div class="layui-inline" style="width: 30%;margin-left: 30px">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>码商提现</legend>
            <br>
            <blockquote class="layui-elem-quote layui-text" style="veritical-align:middle;">
                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商总提现</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 105px">{{$data['code']['done']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商未提现</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 105px">{{$data['code']['balance']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商提现中</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 105px">{{$data['code']['none']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商提现总手续费</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 105px">{{$data['bus']['none']}}元</span>
                </div>
            </blockquote>
            <br>
        </fieldset>
    </div>

    <div class="layui-inline" style="width: 30%;margin-left: 30px">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>码商激活</legend>
            <br>
            <blockquote class="layui-elem-quote layui-text" style="veritical-align:middle;">
                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商激活费用</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 90px">11.00元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商激活佣金</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 90px">11.00元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商激活盈利</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 90px">11.00元</span>
                </div>
            </blockquote>
            <br>
        </fieldset>
    </div>

    <div class="layui-inline" style="width: 30%;margin-left: 30px">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>码商充值</legend>
            <br>
            <blockquote class="layui-elem-quote layui-text" style="veritical-align:middle;">
                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商线上充值</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 90px">{{$data['code_charge']['done']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商上分</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 120px">{{$data['code_charge']['shangfen']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商下分</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 120px">{{$data['code_charge']['xiafen']}}元</span>
                </div>
            </blockquote>
            <br>
        </fieldset>
    </div>

    <div class="layui-inline" style="width: 30%;margin-left: 30px">
        <fieldset class="layui-elem-field site-demo-button">
            <legend>码商充值</legend>
            <br>
            <blockquote class="layui-elem-quote layui-text" style="veritical-align:middle;">
                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商注册人数</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 90px">{{$data['code_charge']['done']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">码商激活人数</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 120px">{{$data['code_charge']['shangfen']}}元</span>
                </div>

                <div class="layui-form-item">
                    <label style="font-size: 15px;">二维码可用数</label>
                    <span class="layui-btn layui-btn-small layui-btn-danger" style="margin-left: 120px">{{$data['code_charge']['xiafen']}}元</span>
                </div>
            </blockquote>
            <br>
        </fieldset>
    </div>


@endsection
@section('table')

@endsection
@section('js')
    <script>
        layui.use(['form', 'jquery','laydate', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer
            ;
            laydate({istoday: true});
            form.render();
            form.on('submit(formDemo)', function(data) {
            });
        });
    </script>
@endsection
@extends('common.list')
