<h1>Something's Broke</h1>
<div>
<?php echo $error ?>: <?php echo $message; ?>
</div>
<div>
<?php echo $file ?>: <?php echo $line; ?>
</div>
<div>
<?php
if($error != 'Error') {
 $backtrace = array_slice(debug_backtrace(), 4); 
	foreach($backtrace as $level => $info) {
		echo "<li>$level: ".(isset($info['file']) ? $info['file'] : 'inline').(isset($info['line']) ? ":".$info['line'] : '')." - ".$info['function'];
	}
}
?>
</div>