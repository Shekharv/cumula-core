	<div class="formItem checkbox">
<?php 
	echo A('FormHelper')->checkboxTag($name, isset($setting['value']) ? $setting['value'] : 'true', $setting['selected']);
	echo A('FormHelper')->labelFor(isset($setting['title']) ? $setting['title'] : $name, $name, array('class' => 'checkbox'));
?>
</div>