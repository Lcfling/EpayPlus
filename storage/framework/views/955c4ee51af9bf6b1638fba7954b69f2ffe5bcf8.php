<?php $__env->startSection('title', '菜单列表'); ?>
<?php $__env->startSection('header'); ?>
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-normal addBtn" data-desc="添加菜单" data-url="<?php echo e(url('/admin/menus/0/edit')); ?>"><i class="layui-icon">&#xe654;</i></button>
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
    <div class="layui-btn layui-btn-small layui-btn-normal zkBtn" data-title="展开菜单"><i class="layui-icon">&#xe602;</i></div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('table'); ?>
    <table class="layui-table" lay-skin="line">
        <colgroup>
            <col width="50">
            <col class="hidden-xs" width="50">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col>
            <col width="130">
        </colgroup>
        <thead>
        <tr>
            <th><input type="checkbox" name="" lay-skin="primary" lay-filter="allChoose"></th>
            <th class="hidden-xs">ID</th>
            <th class="hidden-xs">排序</th>
            <th class="hidden-xs">应用</th>
            <th>菜单名称</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr id='node-<?php echo e($branch['id']); ?>' class="parent collapsed">
                <td><input type="checkbox" name="" lay-skin="primary" data-id="<?php echo e($branch['id']); ?>"></td>
                <td class="hidden-xs"><?php echo e($branch['id']); ?></td>
                <td class="hidden-xs"><input type="number" name="title" autocomplete="off" class="layui-input" value="<?php echo e($branch['order']); ?>" data-id="<?php echo e($branch['id']); ?>" data-url="<?php echo e(url('/admin/sort')); ?>" onchange="changeSort('menus',this)"></td>
                <td class="hidden-xs">Admin</td>
                <td><?php echo e($branch['title']); ?>

                    <a class="layui-btn layui-btn-mini layui-btn-normal showSubBtn" data-id='<?php echo e($branch['id']); ?>'>+</a>
                </td>
                <td>
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-small layui-btn-normal  edit-btn" data-id="<?php echo e($branch['id']); ?>" data-desc="修改菜单" data-url="<?php echo e(url('/admin/menus/'. $branch['id'] .'/edit')); ?>"><i class="layui-icon">&#xe642;</i></button>
                        <button class="layui-btn layui-btn-small layui-btn-danger del-btn" data-id="<?php echo e($branch['id']); ?>" data-url="<?php echo e(url('/admin/menus/'.$branch['id'])); ?>"><i class="layui-icon">&#xe640;</i></button>
                    </div>
                </td>
            </tr>
            <?php if(isset($branch['children'])): ?>
                <?php $__currentLoopData = $branch['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child_branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr id='node-<?php echo e($branch['id']); ?>' class="child-node-<?php echo e($branch['id']); ?> parent collapsed" style="display:none ;" parentid="<?php echo e($branch['id']); ?>">
                        <td><input type="checkbox" name="" lay-skin="primary" data-id="<?php echo e($child_branch['id']); ?>"></td>
                        <td class="hidden-xs"><?php echo e($child_branch['id']); ?></td>
                        <td class="hidden-xs"><input type="text" name="title" autocomplete="off" class="layui-input" value="<?php echo e($child_branch['order']); ?>" data-id="<?php echo e($child_branch['id']); ?>" data-url="<?php echo e(url('/admin/sort')); ?>" onchange="changeSort('menus',this)"></td>
                        <td class="hidden-xs">后台</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─<?php echo e($child_branch['title']); ?></td>
                        <td>
                            <div class="layui-inline">
                                <button class="layui-btn layui-btn-small layui-btn-normal edit-btn" data-id="<?php echo e($child_branch['id']); ?>"  data-desc="修改菜单" data-url="<?php echo e(url('/admin/menus/'. $child_branch['id'] .'/edit')); ?>"><i class="layui-icon">&#xe642;</i></button>
                                <button class="layui-btn layui-btn-small layui-btn-danger del-btn" data-id="<?php echo e($child_branch['id']); ?>" data-url="<?php echo e(url('/admin/menus/'.$child_branch['id'])); ?>"><i class="layui-icon">&#xe640;</i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>
        layui.use(['jquery'], function() {
            var $=layui.jquery;
            //栏目展示隐藏
            $('.showSubBtn').on('click', function() {
                var _this = $(this);
                var id = _this.attr('data-id');
                var parent = _this.parents('.parent');
                var child = $('.child-node-' + id);
                var childAll = $('tr[parentid=' + id + ']');
                if(parent.hasClass('collapsed')) {
                    _this.html('-');
                    parent.addClass('expanded').removeClass('collapsed');
                    child.css('display', '');
                } else {
                    _this.html('+');
                    parent.addClass('collapsed').removeClass('expanded');
                    child.css('display', 'none');
                    childAll.addClass('collapsed').removeClass('expanded').css('display', 'none');
                    childAll.find('.showSubBtn').html('+');
                }
            });
            $('.zkBtn').click(function() {
                if($(this).attr('data-title')=='展开菜单'){
                    $(this).attr('data-title','收缩菜单');
                    $(this).html('<i class="layui-icon">&#xe61a;</i>');
                    $('.showSubBtn').html('-');
                    $('tr').css('display','');
                }else{
                    $(this).attr('data-title','展开菜单');
                    $(this).html('<i class="layui-icon">&#xe602;</i>');
                    $('.showSubBtn').html('+');
                    $("[parentid]").css('display','none');
                }
            }).mouseenter(function() {
                layer.tips($(this).attr('data-title'), $(this),{tips: [3, '#40455C']});
            })
        });
    </script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('common.list', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>