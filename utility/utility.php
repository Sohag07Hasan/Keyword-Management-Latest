<?php
/*
 * wrapper for the classes
 * */

if(!function_exists('cnc_get_the_keyword')){
	
	/*
	 * return keyword of a specific post
	 * can be used inside loop
	 * @post_id, keyword from the specific post will be returned
	 * */
	function cnc_get_the_keyword($post_id = null){
		if(!$post_id){
			global $post;
			$post_id = $post->ID;
		}
		$KwDb = JfKeywordManagement::get_db_instance();
				
		$relation = $KwDb->get_relationship_by('post_id', $post_id);
		$keyword = $KwDb->get_keyword($relation->keyword_id);
		if($keyword){
			return $keyword->keyword;
		}
		else{
			return '';
		}
	}
	
}



if(!function_exists('cnc_the_keyword')){
	
	/*
	 * print the keyword of a specific post
	 * can be used inside loop
	 * @ $post_id, id of the specific post
	 * */
	function cnc_the_keyword($post_id = null){
		echo cnc_get_the_keyword($post_id);
	}
	
}



