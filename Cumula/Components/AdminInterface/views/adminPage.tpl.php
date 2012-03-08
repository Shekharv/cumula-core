<?php
/**
 * Cumula
 *
 * Cumula — framework for the cloud.
 *
 * @package    Cumula
 * @version    0.1.0
 * @author     Seabourne Consulting
 * @license    MIT License
 * @copyright  2011 Seabourne Consulting
 * @link       http://cumula.org
 */

/**
 * adminPage View
 *
 * View that displays the fields in a given admin page.
 *
 * @package		Cumula
 * @subpackage	AdminInterface
 * @author     Seabourne Consulting
 */

?>
<h1><?php echo $cm->page->title ?></h1>
<p><?php if($cm->page->description) echo $cm->page->description; ?></p>
<?php $cm->fh = A('FormHelper'); ?>
<?php echo $cm->fh->formTag('/admin/save_settings', "setting-form-".str_replace(" ", "-", $cm->page->title)) ?>
<fieldset>
<?php 
foreach($cm->page->fields as $setting) {
	?>
	<?php
		echo $cm->render($setting['type'].'SettingField.tpl.php', array('setting' => $setting));
	?><?php
}

echo $cm->fh->hiddenFieldTag('setting-page', $cm->page->route);
echo $cm->fh->submitTag('Save', array('class' => 'button'));
echo '</fieldset>';
echo $cm->fh->formEnd(); ?>