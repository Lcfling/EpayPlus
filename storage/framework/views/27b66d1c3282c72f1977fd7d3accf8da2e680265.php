<?php $__env->startSection('title', '权限编辑'); ?>
<?php $__env->startSection('content'); ?>
    <div class="layui-form-item">
        <label class="layui-form-label">权限标识：</label>
        <div class="layui-input-block">
            <input type="text" value="<?php echo e(isset($info['name']) ? $info['name'] : ''); ?>" name="permission_remark" required lay-verify="permission_remark" placeholder="请输入2-12位字母" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">权限名称：</label>
        <div class="layui-input-block">
            <input type="text" value="<?php echo e(isset($info['display_name']) ? $info['display_name'] : ''); ?>" name="permission_name" required lay-verify="permission_name" placeholder="请输入2-12位汉字" autocomplete="off" class="layui-input">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">权限描述：</label>
        <div class="layui-input-block">
            <textarea name="permission_desc" placeholder="请输入2-30位汉字" class="layui-textarea" required lay-verify="permission_desc"><?php echo e(isset($info['description']) ? $info['description'] : ''); ?></textarea>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">权限控制：</label>
        <div class="layui-input-block">
            <textarea name="permission_control" placeholder="请输入权限控制" class="layui-textarea" required lay-verify="permission_control"><?php echo e(isset($info['controllers']) ? $info['controllers'] : ''); ?></textarea>
            <div class="layui-form-mid layui-word-aux">格式是Controller@method<br>
                Controller为App\Http\Controllers目录下；
                method，可以是get/post，也可以是controller类的方法。</div>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">所属角色：</label>
        <div class="layui-input-block">
            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <input type="checkbox" value="<?php echo e($role['id']); ?>" required <?php echo e(in_array($role['id'],$rolelist)?'checked':''); ?> lay-filter="roles_check" name="permission_roles[]" title="<?php echo e($role['display_name']); ?>">
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
            form.render();
            var layer = layui.layer;
            form.verify({
                permission_remark: [/[a-zA-Z]{2,12}$/, '权限标识必须2到12位字母'],
                permission_name: [/[\u4e00-\u9fa5]{2,12}$/, '权限名称必须2到12位汉字'],
                permission_desc: [/[\u4e00-\u9fa5]{2,30}$/, '权限介绍必须2到30位汉字'],
                permission_control: [/[a-zA-Z][@][get|post]{3,30}$/, '权限控制格式错误'],
            });
            form.on('submit(formDemo)', function(data) {
                var chk_value =[];
                var is_have_admin = 1;
                $('input[name="permission_roles[]"]:checked').each(function(){
                    chk_value.push($(this).val());
                    if($(this).val()==1)is_have_admin--;
                });
                if(chk_value.length==0){
                    layer.msg('至少选择一个所属角色',{shift: 6,icon:5});
                    return false;
                }
                if(is_have_admin){
                    layer.msg('必选选择超级管理员角色',{shift: 6,icon:5});
                    return false;
                }
                $.ajax({
                    url:"<?php echo e(url('/admin/permissions')); ?>",
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