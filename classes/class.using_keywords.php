<?php 
/*
 * This class is to use the keywords in posts pages titles
 * */
class JfKeywordUsing{
	
	static $metaboxes = array();
	
	//constructor
	static function init(){
		add_action('add_meta_boxes', array(get_class(), 'add_new_meta_boxes'));
		add_action('admin_enqueue_scripts', array(get_class() , 'admin_enqueue_scripts'));
		
		add_action('save_post', array(get_class(), 'attach_the_keyword_with_post'), 10, 2);
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
		wp_enqueue_script('jquery');
		wp_register_script('keywords_auto_complete_js', self::get_url('asset/autocompleteui/jquery-ui-1.10.3.custom.min.js'), array('jquery'));
		wp_enqueue_script('keywords_auto_complete_js');
		
		//controller script
		wp_register_script('keywords_auto_complete_controller_js', self::get_url('js/controller.js'), array('jquery'));
		wp_enqueue_script('keywords_auto_complete_controller_js');
		wp_localize_script('keywords_auto_complete_controller_js', 'AjaxAutoComplete', array('ajax_url'=>self::get_url('ajax/autocomplete.php')));
		
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
				
				//$KwDb->remove_previous_relations_by('keyword_id', $keyword->id);
				//$KwDb->remove_previous_relations_by('post_id', $post_id);
				$KwDb->add_new_relations($keyword->id, $post_id);
				
			}
		}
	}
}
