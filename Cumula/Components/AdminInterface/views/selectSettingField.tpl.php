<?php
echo $cm->fh->labelFor($setting['title'], $setting['name']);
echo $cm->fh->selectTag($setting['name'], $setting['values'], $setting['selected']);
?>