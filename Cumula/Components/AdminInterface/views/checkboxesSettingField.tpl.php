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
		echo A('FormHelper')->checkboxTag($name."[]", $option, $selected);
		echo A('FormHelper')->labelFor($label, $name.'-'.$option, array('class' => 'checkbox'));
	?>
	</div>
	<?php } 
?>
