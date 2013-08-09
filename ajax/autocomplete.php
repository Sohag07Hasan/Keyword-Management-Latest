<?php 

require_once "../../../../wp-load.php";

$KwDb = JfKeywordManagement::get_db_instance();
$table_1 = $KwDb->get_keyword_table();
$table_2 = $KwDb->get_keyword_meta_table();
$term = $_GET['term'];

$sql = "select keyword, priority from $table_1 left join $table_2 on $table_1.id = $table_2.keyword_id where $table_2.post_id is null and ( ";

if(preg_match('#"(.*?)"#', $term, $b)){
	$term = $b[1];
	
	$extra = array();
	/*
	$extra[] = "keyword like '% $term%'";
	$extra[] = "keyword like '%$term %'";
	$extra[] = "keyword like '% $term %'";
	*/
	$extra[] = sprintf("keyword REGEXP '[[:<:]]%s[[:>:]]'", $term);
}
else{
	$terms = explode(' ', $term);
	$extra = array();
	foreach($terms as $t){
		$extra[] = "keyword like '%$t%'";
	}
		
}

$sql .= implode(' and ', $extra) . ' ) limit 0, 1000';

$keywords = $KwDb->db->get_results($sql);

$output = array();
foreach($keywords as $keyword){
	$output[] = ucwords($keyword->keyword) . ' ~ ' . $keyword->priority;
}

echo json_encode($output);
exit;

?>