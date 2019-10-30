<?php $__env->startSection('title', '用户列表'); ?>
<?php $__env->startSection('header'); ?>
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-normal addBtn" data-desc="添加用户" data-url="<?php echo e(url('/admin/ucenter/0/edit')); ?>"><i class="layui-icon">&#xe654;</i></button>
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('table'); ?>
    <table class="layui-table" lay-even lay-skin="nob">
        <colgroup>
            <col class="hidden-xs" width="50">
            <col class="hidden-xs" width="200">
            <col class="hidden-xs" width="150">
            <col>
            <col class="hidden-xs" width="150">
            <col width="200">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">ID</th>
            <th class="hidden-xs">头像</th>
            <th class="hidden-xs">用户名</th>
            <th class="hidden-xs">手机号码</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="hidden-xs"><?php echo e($info['uid']); ?></td>
                <td class="hidden-xs"><img style="width: 45px" src="<?php echo e($info['avatar']); ?>"></td>
                <td class="hidden-xs"><?php echo e($info['username']); ?></td>
                <td class="hidden-xs"><?php echo e($info['mobile']); ?></td>
                <td>
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-small layui-btn-normal edit-btn" data-id="<?php echo e($info['uid']); ?>" data-desc="修改用户" data-url="<?php echo e(url('/admin/ucenter/'. $info['uid'] .'/edit')); ?>"><i class="layui-icon">&#xe642;</i></button>
                        <button class="layui-btn layui-btn-small layui-btn-danger del-btn" data-id="<?php echo e($info['uid']); ?>" data-url="<?php echo e(url('/admin/ucenter/'.$info['uid'])); ?>"><i class="layui-icon">&#xe640;</i></button>
                    </div>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php echo e($list->links()); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
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
                console.log(data);
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('common.list', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>