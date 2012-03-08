<?php
foreach($setting['values'] as $key => $option) {
	$label = $setting['labels'][$key];
	if(isset($setting['selected'])) {
		$selected = in_array($option, $setting['selected']);
	} else {
		$selected = false;
	}
	?>
	<div class="formItem checkbox">
	<?php 
		echo $cm->fh->checkboxTag($setting['name']."[]", $option, $selected);
		echo $cm->fh->labelFor($label, $setting['name'].'-'.$option, array('class' => 'checkbox'));
	?>
	</div>
	<?php } 
?>
