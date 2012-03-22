<?php
echo A('FormHelper')->labelFor(isset($setting['title']) ? $setting['title'] : $name, $name);
echo A('FormHelper')->passwordFieldTag($name, (isset($setting['value']) ? $setting['value'] : '') );
?>