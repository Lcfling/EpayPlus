<?php $__env->startSection('title', '角色编辑'); ?>
<?php $__env->startSection('content'); ?>
    <div class="layui-form-item">
        <label class="layui-form-label">角色标识：</label>
        <div class="layui-input-block">
            <input type="text" value="<?php echo e(isset($info['name']) ? $info['name'] : ''); ?>" name="role_remark" required lay-verify="role_remark" placeholder="请输入2-12位字母" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">角色名称：</label>
        <div class="layui-input-block">
            <input type="text" value="<?php echo e(isset($info['display_name']) ? $info['display_name'] : ''); ?>" name="role_name" required lay-verify="role_name" placeholder="请输入2-12位汉字" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">角色描述：</label>
        <div class="layui-input-block">
            <textarea name="role_desc" placeholder="请输入2-30位汉字" class="layui-textarea" required lay-verify="role_desc"><?php echo e(isset($info['description']) ? $info['description'] : ''); ?></textarea>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">角色权限：</label>
        <div class="layui-input-block">
            <input type="checkbox" name="" lay-skin="primary" lay-filter="pAllChoose" title="全选">
        </div>
        <div class="layui-input-block permission">
            <?php $__currentLoopData = $permission; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permise): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <input type="checkbox" name="permission_list[]"
                       <?php if($info): ?>
                       <?php $__currentLoopData = $info->perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($perm->id == $permise['id']?'checked':''); ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                       <?php endif; ?>
                       value="<?php echo e($permise['id']); ?>" lay-skin="primary" title="<?php echo e($permise['display_name']); ?>">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('id',$id); ?>
<?php $__env->startSection('js'); ?>
    <script>
        layui.use(['form','jquery','laypage', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery;
            form.on('checkbox(pAllChoose)', function(data) {
                var child = $(".permission").find('input[type="checkbox"]');
                child.each(function(index, item) {
                    item.checked = data.elem.checked;
                });
                if(data.elem.checked)$(this).attr('title','全不选');
                else $(this).attr('title','全选');
                form.render('checkbox');
            });

            form.render();
            var layer = layui.layer;
            form.verify({
                role_desc: [/[a-zA-Z]{2,12}$/, '角色描述必须2到12位字母'],
                role_remark: [/[a-zA-Z]{2,12}$/, '角色标识必须2到12位字母'],
                role_name: [/[\u4e00-\u9fa5]{2,12}$/, '角色名称2到12位汉字'],
                role_desc: [/[\u4e00-\u9fa5]{2,30}$/, '角色描述2到30位汉字'],
            });
            form.on('submit(formDemo)', function(data) {
                var chk_value =[];
                $('input[name="permission_list[]"]:checked').each(function(){
                    chk_value.push($(this).val());
                });
                if($("input[type='permission_list[]']").length>0&&chk_value.length==0){
                    layer.msg('至少选择一个角色权限',{shift: 6,icon:5});
                    return false;
                }
                $.ajax({
                    url:"<?php echo e(url('/admin/roles')); ?>",
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
<?php $__env->stopSection(); ?>


<?php echo $__env->make('common.edit', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>