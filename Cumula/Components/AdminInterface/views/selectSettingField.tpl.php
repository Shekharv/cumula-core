<?php
echo $this->fh->labelFor($setting['title'], $setting['name']);
echo $this->fh->selectTag($setting['name'], $setting['values'], $setting['selected']);
?>