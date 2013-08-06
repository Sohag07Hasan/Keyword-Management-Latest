<?php 

require_once "../../../../wp-load.php";

$KwDb = JfKeywordManagement::get_db_instance();
$table = $KwDb->get_keyword_table();
$term = $_GET['term'];

$terms = explode(' ', $term);

$sql = "select keyword, priority from $table where status = '1' and ( ";

$extra = array();
foreach($terms as $t){
	$extra[] = "keyword like '%$t%'";
}
			
$sql .= implode(' or ', $extra) . ' ) limit 0, 1000';

$keywords = $KwDb->db->get_results($sql);

$output = array();
foreach($keywords as $keyword){
	$output[] = ucwords($keyword->keyword) . ' ~ ' . $keyword->priority;
}

echo json_encode($output);
exit;

?>