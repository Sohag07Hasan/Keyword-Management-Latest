<?php 
/**
 * Plugin Name: Keyword Management
 * Author: Mahibul Hasan
 * */

define("JFKEYWORDMANAGEMENT_FILE", __FILE__);
define("JFKEYWORDMANAGEMENT_DIR", dirname(__FILE__) . '/');
define("JFKEYWORDMANAGEMENT_URL", plugins_url('/', __FILE__));

include JFKEYWORDMANAGEMENT_DIR . 'classes/class.keyword.management.php';
JfKeywordManagement::init();

?>