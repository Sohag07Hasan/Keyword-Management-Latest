<style>
	.ui-autocomplete {
	    max-height: 300px;
	    overflow-y: auto;
	    /* prevent horizontal scrollbar */
	    overflow-x: hidden;
	  }
	  /* IE 6 doesn't support max-height
	   * we use height instead, but this forces the menu to always be this tall
	   */
	  html .ui-autocomplete {
	    height: 300px;
	  }
</style>

<?php 
	$KwDb = JfKeywordManagement::get_db_instance();
	$relation = $KwDb->get_relationship_by('post_id', $post->ID);
	$keyword = $KwDb->get_keyword($relation->keyword_id);
	if($keyword){
		$keyword_text = ucwords($keyword->keyword) . ' ~ ' . $keyword->priority;
	}
	else{
		$keyword_text = '';
	}
?>

<div class="wrap">
	<p><input placeholder="Keyword" style="width: 100%; font-size: 16px;" id="unique_keyword" type="text" name="keyword_keyword" value="<?php echo $keyword_text; ?>" /></p>
</div>

<!-- extra input fields to hold the previous entries of keyword -->
<input type="hidden" id="previous_keyword_keyword" value="" />