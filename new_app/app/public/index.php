<?php

require '../../vendor/.composer/autoload.php';

define('APPROOT', realpath(implode(DIRECTORY_SEPARATOR, array(__DIR__, '..'))));

include(realpath(implode(DIRECTORY_SEPARATOR, array(APPROOT, '..', 'vendor', 'cumula', 'core', 'bin', 'boot.php'))));