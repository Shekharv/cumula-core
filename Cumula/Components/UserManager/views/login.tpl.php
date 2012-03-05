<?php echo \I('Session')->warning; ?>
<?php echo \I('Session')->notice; ?>
<h1>Login</h1>

<?php $form = \I('FormHelper'); ?>
<?php echo $form->formTag('/user/authenticate', 'user-form'); ?>
<div><?php echo $form->labelFor('Username', 'user-form-username').$form->textFieldTag('username'); ?></div>
<div><?php echo $form->labelFor('Password', 'user-form-password').$form->passwordFieldTag('password'); ?></div>
<?php echo $form->submitTag('Login'); ?>
<?php echo $form->formEnd(); ?>
