<?php

define('EXPORTER_DIR', realpath(dirname(__FILE__)) );
define('EXPORTER_OUTPUT_DIR', EXPORTER_DIR . '/output' );
define('EXPORTER_OUTPUT_URL', 'http://plnitherm-network.loc/cms/modules/exporter/output');

require_once( EXPORTER_DIR . '/config/config.php' );
require_once( EXPORTER_DIR . '/lib/ajax.class.php' );
require_once( EXPORTER_DIR . '/lib/exporter.class.php' );
