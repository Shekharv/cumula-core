	<h2>Installation: Step 2</h2>
	
	<?php if (!in_array(FALSE, $this->perms)) { ?>
	<p>We've performed an initial system check and it looks like your system is ready to support Cumula.  Results are below.</p>
	<?php } else {?>
	<p>We've performed an initial system check and there were some problems.  Please see below for more details.</p>
	<?php } ?>
<section id="messages" class="box">
	<h3>System notes</h3>
	<ul>
		<?php if (PHP_VERSION_ID > 50300) { ?>
		<li class="success">Cumula will work with your version of PHP (<?php echo PHP_VERSION ?>).</li>
		<?php } ?>
		<!--<li class="error">Some library <span class="notes">You must install some library, follow <a href="#">these instructions</a> in the documentation</span></li>-->
		<?php if(in_array(FALSE, $this->perms)) { ?>
			<li class="error">File Permissions Need Help. Follow <a href="#">these instructions</a> to fix this.</span> 
				<ul>
					<? foreach($this->perms as $file => $value) {
						$readable = is_readable($file);
						$writable = is_writable($file);
						if(!$value) {
							if(!$readable)
								echo "<li><strong>$file</strong> is not readable.</li>";
							if(!$writable)
								echo "<li><strong>$file</strong> is not writable.</li>";
						}
							
					}?>
				</ul>
			</li>
		<?php } else { ?>
			<li class="success">File Permissions Are All Good.</li>
		<?php } ?>
		<?php if($this->rewrite == true) { ?>
			<li class='success'>Apache mod_rewrite is enabled.</li>
		<?php } else { ?>
			<li class='error'>Apache mod_rewrite is not enabled.  You will not be able to use clean URLs.</li>
		<?php } ?>
	</ul>
</section>
	<p class="green"><?php echo $this->linkTo('Next &#187;', '/install/setup_user'); ?></p>