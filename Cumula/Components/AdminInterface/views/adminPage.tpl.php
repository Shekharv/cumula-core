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
<h1><?php echo $title ?></h1>
<p><?php if(isset($page['description'])) echo $page['description']; ?></p>
<?php echo $fh->formTag($savePath, "setting-form-".str_replace(" ", "-", $title)) ?>
<fieldset>
<?php 
if(isset($page['fields'])) {
foreach($page['fields'] as $name => $setting) {
	?>
	<?php
		echo $this->renderView($setting['type'].'SettingField.tpl.php', array('name' => $name, 'setting' => $setting));
	?><?php
}
}
echo $fh->hiddenFieldTag('setting-page', $startPath);
if(count($page['fields']) > 0)
	echo $fh->submitTag('Save', array('class' => 'button'));
echo '</fieldset>';
echo $fh->formEnd(); ?>