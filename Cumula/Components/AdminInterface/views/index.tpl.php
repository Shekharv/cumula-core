<h1>Dashboard</h1>
<section id="messages" class="box">
	<h1>System notes</h1>
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
	</ul>
</section>

<section class="box">
	<h1>System Summary</h1>
	<ul>
		<li>Running Cumula Version <strong><?php echo CUMULAVERSION ?></strong>.</li>
		<li><strong><?php echo $this->stats['installed_components'] ?></strong> components enabled.</li>
	</ul>
</section>

<section class="box">
	<h1>Resources</h1>
	<ul>
		<li><a href="https://github.com/Cumula/CumulaFramework/wiki/Cumula-Basics">Cumula Basics</a></li>
		<li><a href="https://github.com/Cumula/CumulaFramework/wiki/Hello-world">Hell World</a></li>
		<li><a href="https://github.com/Cumula/CumulaFramework/wiki/Developing-a-component">Developing a Component</a></li>
	</ul>
</section>
