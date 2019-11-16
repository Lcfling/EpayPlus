@section('title', '激活佣金')
@section('header')
    <div class="layui-inline">
        <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-even lay-skin="nob">
        <colgroup>
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col class="hidden-xs" width="150">
            <col width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">激活佣金</th>
            <th class="hidden-xs">1级返佣</th>
            <th class="hidden-xs">2级返佣</th>
            <th class="hidden-xs">3级返佣</th>
            <th class="hidden-xs">4级返佣</th>
            <th class="hidden-xs">5级返佣</th>
            <th class="hidden-xs">6级返佣</th>
            <th class="hidden-xs">7级返佣</th>
            <th class="hidden-xs">8级返佣</th>
            <th class="hidden-xs">9级返佣</th>
            <th class="hidden-xs">10级返佣</th>
            <th class="hidden-xs">时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>

                <td class="hidden-xs">{{$info['jhmoney']/100}}</td>
                <td class="hidden-xs">{{$info['fymoney1']/100}}</td>
                <td class="hidden-xs">{{$info['fymoney2']/100}}</td>
                <td class="hidden-xs">{{$info['fymoney3']/100}}</td>
                <td class="hidden-xs">{{$info['fymoney4']/100}}</td>
                <td class="hidden-xs">{{$info['fymoney5']/100}}</td>
                <td class="hidden-xs">{{$info['fymoney6']/100}}</td>
                <td class="hidden-xs">{{$info['fymoney7']/100}}</td>
                <td class="hidden-xs">{{$info['fymoney8']/100}}</td>
                <td class="hidden-xs">{{$info['fymoney9']/100}}</td>
                <td class="hidden-xs">{{$info['fymoney10']/100}}</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
                <td>
                    <div class="layui-inline">
                        <a class="layui-btn layui-btn-small layui-btn-normal" onclick="edit({{$info['id']}})">编辑</a>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection
@section('js')
    <script>
        layui.use(['form', 'jquery', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery,
                layer = layui.layer;
            form.render();
            form.on('submit(formDemo)', function(data) {
                console.log(data);
            });
            //layer.msg(layui.v);
        });
        function edit(id) {
            var id=id;
            layer.open({
                type: 2,
                title: '激活佣金',
                closeBtn: 1,
                area: ['500px','700px'],
                shadeClose: false, //点击遮罩关闭
                resize:false,
                content: ['/admin/coderakemoney/'+id+'/edit','no'],
                end:function(){

                }
            });
        }
    </script>
@endsection
@extends('common.list')