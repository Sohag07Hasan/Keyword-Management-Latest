<?php 
	
	$KeywordList = self::get_list_table();
	
?>

<div class="wrap">
	<h2>Keywords
		<a href="<?php echo admin_url('admin.php?page=addnew_keyword'); ?>" class="add-new-h2">Add New</a>
		<?php 
			if(isset($_REQUEST['s']) && !empty($_REQUEST['s'])){
				echo '<span class="subtitle">Search results for “'.$_REQUEST['s'].'”</span>';
			}
		?>
	</h2>
	
	<?php 
		if($_REQUEST['deleted'] > 0) echo '<div class="updated"><p>' . $_REQUEST['deleted'] . ' deleted! </p></div>';
	?>
	
	<form action="" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
		<input type="hidden" name="keyword_table_bulk_action" value="y" />
		<?php
			$KeywordList->prepare_items();
			$KeywordList->search_box('Search', 'keyword');
			$KeywordList->display();
		?>
	</form>
	
</div>