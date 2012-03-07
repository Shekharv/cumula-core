<?php 
/**
 *  @package Cumula
 *  @subpackage Core
 *  @version    $Id$
 */

const SETTING_COMPONENT_MANAGER = 'component_manager';
const DEFAULT_COMPONENT_MANAGER_CLASS = 'Cumula\\ComponentManager';

const APPLICATION_CLASS = 'Cumula\\Application';

const DEFAULT_ALIAS_MANAGER_CLASS = 'Cumula\\AliasManager';

const DEFAULT_REQUEST_MANAGER_CLASS = 'Cumula\\Request';

const DEFAULT_RESPONSE_MANAGER_CLASS = 'Cumula\\Response';

const DEFAULT_SYSTEM_CONFIG_CLASS = 'Cumula\\SystemConfig';

const DEFAULT_AUTOLOADER_CLASS = 'Cumula\\Autoloader';

const DEFAULT_TEMPLATER_CLASS = 'Cumula\\Templater';

const DEFAULT_ROUTER_CLASS = 'Cumula\\Router';

const SETTING_DEFAULT_DATASTORE = 'default_datastore';
const DEFAULT_DATASTORE_CLASS = '\\Cumula\\DataStore\\YAML';

const SETTING_DEFAULT_CONFIG = 'default_config';
const DEFAULT_CONFIG_CLASS = 'Cumula\\Config\\Standard';

const SETTING_DEFAULT_BASE_PATH = 'default_base_path';
const DEFAULT_SITE_BASE_PATH = '/index.php';

const ENV_DEVELOPMENT = 'development';
const ENV_TEST = 'test';
const ENV_PRODUCTION = 'production';

const SETTING_ENVIRONMENT = 'setting_environment';
const DEFAULT_ENVIRONMENT = ENV_DEVELOPMENT;

const SETTING_SITE_URL = 'site_url';

const SETTING_SITE_TITLE = 'site_title';
const DEFAULT_SITE_TITLE = 'Welcome to Cumula';

const LOG_LEVEL_ALL = 'All';
const LOG_LEVEL_DEBUG = 'DEBUG';
const LOG_LEVEL_INFO = 'INFO';
const LOG_LEVEL_WARN = 'WARNING';
const LOG_LEVEL_ERROR = 'ERROR';
const LOG_LEVEL_FATAL = 'FATAL';
