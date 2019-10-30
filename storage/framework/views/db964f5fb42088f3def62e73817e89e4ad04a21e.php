<?php $__env->startSection('title', '用户编辑'); ?>
<?php $__env->startSection('content'); ?>
    <div class="layui-form-item">
        <label class="layui-form-label">用户名：</label>
        <div class="layui-input-block">
            <input type="text" value="<?php echo e(isset($info['username']) ? $info['username'] : ''); ?>" name="user_name" required lay-verify="user_name" placeholder="用户名必须2到12位字母" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">邮箱：</label>
        <div class="layui-input-block">
            <input type="text" value="<?php echo e(isset($info['email']) ? $info['email'] : ''); ?>" name="email" required lay-verify="required|email" placeholder="请输入正确格式邮箱" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">角色：</label>
        <div class="layui-input-block">
            <select name="user_role" lay-verify="required">
                <option value=""></option>
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option
                            <?php if(isset($info)&&$info): ?>
                            <?php $__currentLoopData = $info->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uRole): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($role->id == $uRole->id): ?>
                            <?php echo e('selected'); ?>

                            <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                            value="<?php echo e($role->id); ?>"><?php echo e($role->display_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">手机号码：</label>
        <div class="layui-input-block">
            <input type="number" value="<?php echo e(isset($info['mobile']) ? $info['mobile'] : ''); ?>" name="tel" required lay-verify="required|phone" placeholder="请输入手机号码" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">性别：</label>
        <div class="layui-input-block">
            <input type="radio" name="sex" value="1" title="男"
                   <?php if(!isset($info['sex'])): ?>
                   checked
                   <?php elseif(isset($info['sex'])&&$info['sex']): ?>
                   checked
            <?php else: ?>
                    <?php endif; ?>>
            <input type="radio" name="sex" value="0" title="女" <?php echo e(isset($info['sex'])&&!$info['sex']?'checked':''); ?>>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">密码：</label>
        <div class="layui-input-block">
            <input type="password" name="pwd" lay-verify="pwd" placeholder="请输入6-12位数字加字母密码" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">确认密码：</label>
        <div class="layui-input-block">
            <input type="password" name="pwd_confirmation" lay-verify="pwd_confirmation" placeholder="请确认密码" autocomplete="off" class="layui-input">
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
                user_name: [/^[0-9a-zA-Z]{2,12}$/, '用户名必须2到12位字母'],
                pwd:function(value){
                    if(value&&!/^(?!([a-zA-Z]+|\d+)$)[a-zA-Z\d]{6,12}$/.test(value)){
                        return '密码必须6到12位数字加字母';
                    }
                },
                pwd_confirmation: function(value) {
                    if($("input[name='pwd']").val() && $("input[name='pwd']").val() != value) {
                        return '两次输入密码不一致';
                    }
                },
            });
            form.on('submit(formDemo)', function(data) {
                $.ajax({
                    url:"<?php echo e(url('/admin/users')); ?>",
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