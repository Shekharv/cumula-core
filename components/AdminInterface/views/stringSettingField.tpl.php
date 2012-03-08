<?php
echo $cm->fh->labelFor($setting['title'], $setting['name']);
echo $cm->fh->textFieldTag($setting['name'], (isset($setting['value']) ? $setting['value'] : '') );
?>