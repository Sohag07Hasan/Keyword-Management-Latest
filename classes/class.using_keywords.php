<?php 
/*
 * This class is to use the keywords in posts pages titles
 * */
class JfKeywordUsing{
	
	static $metaboxes = array();
	
	//constructor
	static function init(){
		//add metaboxes
		//add_action('add_meta_boxes', array(get_class(), 'add_new_meta_boxes'));
		
		//metabox position\
		//add_action('edit_form_after_title', array(get_class(), 'positioning_the_meta_boxes'));
		
		//keyword fields before title div
		add_action('edit_form_after_title', array(get_class(), 'attach_meta_box_before_title'));
		
		add_action('admin_enqueue_scripts', array(get_class() , 'admin_enqueue_scripts'));
		
		add_action('save_post', array(get_class(), 'attach_the_keyword_with_post'), 10, 2);
		
		//unattach the keyword with post
		add_action('trashed_post', array(get_class(), 'unattach_the_keyword'), 10, 1);
		add_action('after_delete_post', array(get_class(), 'unattach_the_keyword'), 10, 1);
		
		
		//post save checking
		add_action('admin_init', array(get_class(), 'before_post_saving'));
		
		
		//show admin notices
		add_action('admin_notices', array(get_class(), 'show_keyword_failed_notices'));
		
				
		//add_action('init', array(get_class(), 'test'));
	}
	
	
	function test(){
		var_dump(cnc_get_the_keyword(98));
		cnc_the_keyword(98);
		exit;
	}
	
	//positioning the meta boxes
	static function positioning_the_meta_boxes(){
		// Get the globals:
		global $post, $wp_meta_boxes;
		
		// Output the "advanced" meta boxes:
		do_meta_boxes(get_current_screen(), 'advanced', $post);
		
		// Remove the initial "advanced" meta boxes:
		unset($wp_meta_boxes['post']['advanced']);
	}
	
	
	//attach metabox before title 
	static function attach_meta_box_before_title(){
		global $post;
		if($post->post_type == 'post' && current_user_can('use_keywords')){
			return self::key_word_field($post);
		}
	}
	
	
	//metabox addition
	static function add_new_meta_boxes(){
		self::$metaboxes[] = array(
			'id' => 'add_key_word',
			'title' => 'Use Keyword',
			'callback' => array(get_class(), 'key_word_field'),
			'post_type' => 'post',
			'context' => 'advanced',
			'priority' => 'high'				
		);
		
		foreach(self::$metaboxes as $mbox){
			add_meta_box($mbox['id'], $mbox['title'], $mbox['callback'], $mbox['post_type'], $mbox['context'], $mbox['priority']);
		}
	}
	
	
	//metabox content
	static function key_word_field($post){
		include JfKeywordManagement::abspath_for_script('includes/metaboxes/key_word_field.php');
	}
	
	
	//enqueue script and 
	static function admin_enqueue_scripts(){
		//auto complete jquery
		wp_enqueue_script('jquery');
		wp_register_script('keywords_auto_complete_js', self::get_url('asset/autocompleteui/jquery-ui-1.10.3.custom.min.js'), array('jquery'));
		wp_enqueue_script('keywords_auto_complete_js');
		
		//controller script
		wp_register_script('keywords_auto_complete_controller_js', self::get_url('js/controller.js'), array('jquery'));
		wp_enqueue_script('keywords_auto_complete_controller_js');
		wp_localize_script('keywords_auto_complete_controller_js', 'AjaxAutoComplete', array('ajax_url'=>self::get_url('ajax/autocomplete.php')));
		
		//controller css
		wp_register_style('keywords_auto_complete_controller_css', self::get_url('css/keyword.css'));
		wp_enqueue_style('keywords_auto_complete_controller_css');
		
		//auto complete css
		wp_register_style('keywords_auto_complete_css', self::get_url('asset/autocompleteui/jquery-ui-1.10.3.custom.min.css'));
		wp_enqueue_style('keywords_auto_complete_css'); 
		
		
	}
	
	
	//get the url of the scripts
	static function get_url($script = ''){
		return JFKEYWORDMANAGEMENT_URL . $script;
	}
	
	
	//save the keyword with post
	static function attach_the_keyword_with_post($post_id, $post){	
		
		if(!wp_is_post_revision( $post_id )){
						
			if(isset($_POST['keyword_keyword']) && !empty($_POST['keyword_keyword'])){
							
				$key = explode(' ~ ', $_POST['keyword_keyword']);
				$keyword_name = trim($key[0]);
				
				$KwDb = JfKeywordManagement::get_db_instance();
				$keyword = $KwDb->get_keyword_by_keyword($keyword_name);
				if($keyword){								
					return $KwDb->add_new_relations($keyword->id, $post_id);
				}
				
			}
			elseif(isset($_POST['keyword_keyword']) && empty($_POST['keyword_keyword'])){
				$KwDb = JfKeywordManagement::get_db_instance();
				
				$keyword_name = $post->post_title;
				$keyword = $KwDb->get_keyword_by_keyword($keyword_name);
				if(!$keyword){
					$id = $KwDb->create_keyword(array('keyword' => $keyword_name, 'priority' => 10));
					$keyword = $KwDb->get_keyword($id);
				}
				
				if($keyword){
					return $KwDb->add_new_relations($keyword->id, $post_id);
				}
			}
		}
	}
	
	
	//un attach the post if a post get trashed or delted
	static function unattach_the_keyword($post_id){
		$KwDb = JfKeywordManagement::get_db_instance();
		return $KwDb->remove_previous_relations_by('post_id', $post_id);
	}
	
	
	/*
	 * before post saving
	 * */
	static function before_post_saving(){
		if(isset($_POST['keyword_keyword'])){
			if(empty($_POST['keyword_keyword'])){
								
				$KwDb = JfKeywordManagement::get_db_instance();
				
				$keyword_name = $_POST['post_title'];
				$keyword = $KwDb->get_keyword_by_keyword($keyword_name);
				
				$relation = $KwDb->get_relationship_by('keyword_id', $keyword->id);
				if($relation){
					if($relation->post_id == $_POST['post_ID']){
						return;
					}
					else{
						$sendback = $_POST['_wp_http_referer'];
						$sendback = add_query_arg(array('message' => 15, 'keyword_status' => 'unattached'), $sendback);
						
						return JfKeywordManagement::do_redirect($sendback);
					}
				}
			}
		}
	}
	
	
	/**
	 * if a keyword attahcment is failed show the message
	 * */
	static function show_keyword_failed_notices(){
		if($_REQUEST['keyword_status'] == 'unattached'){
			if($_REQUEST['message'] == '15'){
				echo '<div class="updated"><p>Sorry! Keyword cannot be attached! Duplicate found</p></div>';
			}
		}
	}
	
	
	/**
	 * Manage capabilities
	 * */
	static function manage_keyword_capabilities(){
		$roles = array('administrator', 'editor', 'author');
		foreach($roles as $r){
			$role = get_role($r);
			if($role){
				$role->add_cap('use_keywords');
			}
		}
				
	}
	
}
