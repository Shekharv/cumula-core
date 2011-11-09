	<h2>Installation: Step 3</h2>
	
	<p>Create an admin user account.  This account will allow you to access the Cumula administration interface.</p>
<?php $fh = \Cumula\Application::getFormHelper(); ?>


<?php echo $fh->formTag('/install/save_user', "save-user-form"); 
echo '<fieldset>';
echo "<div>".$fh->labelFor('Username: ', 'save-user-form-username').$fh->textFieldTag('username')."</div>";
echo "<div>".$fh->labelFor('Password: ', 'save-user-form-password').$fh->passwordFieldTag('password')."</div>";
echo "<div>".$fh->labelFor('Confirm Password: ', 'save-user-form-passconf').$fh->passwordFieldTag('passconf')."</div>";
echo "<p class=\"green\">".$fh->submitTag('Save')."</p>";
echo '</fieldset>';
echo $fh->formEnd(); ?>

