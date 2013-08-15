<?php 
/**
 * Plugin Name: Keyword Management
 * Author: Mahibul Hasan
 * Author uri: http://sohag07hasan.elance.com
 * Description: Creates, manage keywords to make the post title unique
 * */

define("JFKEYWORDMANAGEMENT_FILE", __FILE__);
define("JFKEYWORDMANAGEMENT_DIR", dirname(__FILE__) . '/');
define("JFKEYWORDMANAGEMENT_URL", plugins_url('/', __FILE__));

include JFKEYWORDMANAGEMENT_DIR . 'classes/class.keyword.management.php';
JfKeywordManagement::init();

include JFKEYWORDMANAGEMENT_DIR . 'classes/class.using_keywords.php';
JfKeywordUsing::init();


//wrapper and utility
include JFKEYWORDMANAGEMENT_DIR . 'utility/utility.php';

?>