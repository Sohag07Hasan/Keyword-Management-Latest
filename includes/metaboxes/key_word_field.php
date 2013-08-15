
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
<div id="add_key_word_container">
	<div id="add_key_word" class="postbox">
		<h3 class="hndle">
			<span>Keyword</span>
		</h3>
		<div class="inside">
			<div class="wrap">
				<p><input placeholder="Keyword" style="width: 100%; font-size: 16px;" id="unique_keyword" type="text" name="keyword_keyword" value="<?php echo $keyword_text; ?>" /></p>
			</div>
		</div>
	</div>
</div>
<!-- extra input fields to hold the previous entries of keyword -->
<input type="hidden" id="previous_keyword_keyword" value="" />