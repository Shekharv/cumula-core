<h1>Register</h1>
<?php echo $this->form->formTag('/user/save', 'register-form'); ?>
<div>Email: <?php echo $this->form->textFieldTag('email'); ?></div>
<div>Password: <?php echo $this->form->passwordFieldTag('password'); ?></div>
<div>Confirm Password: <?php echo $this->form->passwordFieldTag('confirm-password'); ?></div>
<div><?php echo $this->form->submitTag('Register'); ?></div>
<?php echo $this->form->formEnd(); ?>