<?php 
/*
 * cretaes admin menu, submenu
 * handle csv uploding with separte csv uploader
 * 
 * */
class JfKeywordManagement{
	
	//initialize
	static function init(){
		add_action('admin_menu', array(get_class(), 'admin_menu'));
		add_action('admin_init', array(get_class(), 'form_submission_handler'));
		register_activation_hook(JFKEYWORDMANAGEMENT_FILE, array(get_class(), 'manage_db'));
	}
	
	//creating menu and submenu pages
	static function admin_menu(){
		add_menu_page('tf keyword management', 'KeyWords', 'manage_options', 'keyword_manager', array(get_class(), 'menu_page_keyword_management'));
		add_submenu_page('keyword_manager', ucwords('new or edit a keyword'), 'Add New', 'manage_options', 'addnew_keyword', array(get_class(), 'submenu_add_or_edit_keyword'));
		add_submenu_page('keyword_manager', ucwords('import keywords'), 'Import', 'manage_options', 'import_keyword', array(get_class(), 'submenu_import_keywords'));
		add_submenu_page('keyword_manager', ucwords('export keywords'), 'Export', 'manage_options', 'export_keyword', array(get_class(), 'submenu_export_keywords'));
	}
	
	
	
	//menu page for keyword management
	static function menu_page_keyword_management(){
		include self::abspath_for_script('includes/menu-submenu/menupage-keyword-management.php');
	}	
	
		
	//submenu to get add or edit screen
	static function submenu_add_or_edit_keyword(){
		include self::abspath_for_script('includes/menu-submenu/submenu-add-edit-keyword.php');
	}
	
	//import keywords
	static function submenu_import_keywords(){
		include self::abspath_for_script('includes/menu-submenu/submenu-import-keywords.php');
	}

	//export keywrods
	static function submenu_export_keywords(){
		include self::abspath_for_script('includes/menu-submenu/submenu-keywords-export.php');
	}
	
	//function to include script
	static function abspath_for_script($script = ''){
		return JFKEYWORDMANAGEMENT_DIR . $script;
	}
	
	
	
	//form submission handler
	static function form_submission_handler(){
		
		//add or edit new keyword
		if($_POST['page'] == 'addnew_keyword'){
			$url = admin_url('admin.php?page=addnew_keyword');
			$info = array();
			$KwDb = self::get_db_instance();

			if(empty($_POST['keyword']['keyword']) || empty($_POST['keyword']['priority'])) return;

			$keyword_id = $KwDb->create_keyword($_POST['keyword']);
			if($keyword_id > 0){
				$info['keyword_id'] = $keyword_id;
				$info['message'] = 1;
			}
			else{
				$info['keyword_id'] = 0;
				$info['message'] = 2;
			}
			
			$url = add_query_arg($info, $url);
			return self::do_redirect($url);
		}
		
		
		//export csv
		if($_POST['keyword_export_csv'] == 'y'){
			$KwDb = self::get_db_instance();
			$keywords = $KwDb->get_keywords_for_csv();
			$d_array = array();
			
			if($keywords){
				foreach($keywords as $keyword){
					
					$is_used = $KwDb->is_used($keyword->id);
					
					$d_array[] = array(
						'Keyword' => $keyword->keyword,
						'Priority' => $keyword->priority,
						'Status' => $is_used ? 'Used' : ''		
					);
				}
			}
			
			$csv = self::get_csv_parser();
			$csv->output('keywords.csv', $d_array, array('Keyword', 'Priority', 'Status'), ',');
			exit;
		}
		
		
		// list table's bulk action
		if($_REQUEST['keyword_table_bulk_action'] == 'y' || ($_REQUEST['page'] == 'keyword_manager' && $_REQUEST['action'] == 'delete')){
			
			$sendback = remove_query_arg( array('deleted', 'keyword_id', 'keyword_table_bulk_action'), wp_get_referer() );
						
			if(!$sendback){
				$sendback = admin_url('admin.php?page=keyword_manager');
			}
			
			$wp_list_table = self::get_list_table();
			$doaction = $wp_list_table->current_action();
			$pagenum = $wp_list_table->get_pagenum();
			
			$sendback = add_query_arg( 'paged', $pagenum, $sendback );
			
			if($doaction == 'delete'){
				$deleted = $wp_list_table->handle_bulk_action();
				$sendback = add_query_arg('deleted', $deleted, $sendback);
			}
			
			$sendback = remove_query_arg( array('action', 'action2', '_wp_http_referer', '_wpnonce'), $sendback );
			if(!empty($_REQUEST['s'])){
				$sendback = add_query_arg( 's', urlencode($_REQUEST['s']), $sendback );
			}
			
			return self::do_redirect($sendback);
		}		
		
	}
	
	//do a redirect
	static function do_redirect($url){
		if(!function_exists('wp_redirect')){
			include ABSPATH . '/wp-includes/pluggable.php';
		}		
		wp_redirect($url);
		die();
	}
	
	
	//get db instance
	static function get_db_instance(){
		if(!class_exists('JfKeywordDb')){
			include self::abspath_for_script('classes/class.db.php');
		}		
		return new JfKeywordDb();
	}
	
	//get list table instance
	static function get_list_table(){
		if(!class_exists('JfKeywordListTable')){
			include self::abspath_for_script('classes/class.list-table.php');
		}
		
		return new JfKeywordListTable();
	}
	
	//get csv class instance
	static function get_csv_parser(){
		if(!class_exists('parseCSV')){
			include self::abspath_for_script('classes/parsecsv.lib.php');
		}
		
		return new parseCSV(); 
	}
	
	
	//plgugin activation
	static function manage_db(){
		$KwDb = self::get_db_instance();
		return $KwDb->sync_db();
	}	
}