<?php 
/**
 *  @package Cumula
 *  @subpackage Core
 *  @version    $Id$
 */

//TODO: Replace with function to dynamically load everything in the core folder

require_once dirname(__FILE__) .'/../I.php';
//Load Constants
require_once dirname(__FILE__) . '/constants.inc';

//Load utility functions
require_once dirname(__FILE__) . '/inflect.php';
require_once dirname(__FILE__) . '/utils.php';

require_once dirname(__FILE__) .'/../Error.php';
