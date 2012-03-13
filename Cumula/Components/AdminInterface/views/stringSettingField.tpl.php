<?php
echo A('FormHelper')->labelFor(isset($setting['title']) ? $setting['title'] : $name, $name);
echo A('FormHelper')->textFieldTag($name, (isset($setting['value']) ? $setting['value'] : '') );
?>