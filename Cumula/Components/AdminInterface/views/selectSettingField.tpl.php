<?php
echo A('FormHelper')->labelFor($setting['title'], $name);
echo A('FormHelper')->selectTag($name, $setting['values'], $setting['selected']);
?>