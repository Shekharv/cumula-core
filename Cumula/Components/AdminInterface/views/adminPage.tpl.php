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
<h1><?php echo $this->page->title ?></h1>
<p><?php if($this->page->description) echo $this->page->description; ?></p>
<?php echo $this->fh->formTag('/admin/save_settings', "setting-form-".str_replace(" ", "-", $this->page->title)) ?>
<fieldset>
<?php 
foreach($this->page->fields as $setting) {
	?>
	<?php
		echo $this->renderPartial($setting['type'].'SettingField', array('setting' => $setting));
	?><?php
}

echo $this->fh->hiddenFieldTag('setting-page', $this->page->route);
echo $this->fh->submitTag('Save');
echo '</fieldset>';
echo $this->fh->formEnd(); ?>