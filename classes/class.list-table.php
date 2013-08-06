<?php 

if( ! class_exists( 'WP_List_Table' ) ) {
	if(!class_exists('WP_Internal_Pointers')){
		require_once( ABSPATH . '/wp-admin/includes/template.php' );
	}
	require_once( ABSPATH . '/wp-admin/includes/class-wp-list-table.php' );
}

class JfKeywordListTable extends WP_List_Table{
	private $per_page;
	private $total_items;
	private $current_page;	
	public $KwDb;
		
	function __construct(){
		$this->KwDb = JfKeywordManagement::get_db_instance();
		parent::__construct();
	}
	
	/*preparing items must overwirte the mother function*/
	function prepare_items(){
			
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
	
		$this->_column_headers = array($columns, $hidden, $sortable);
	
		//paginations
		$this->_set_pagination_parameters();
	
		//every elements
		$this->items = $this->populate_table_data();	
	}
	
	
	//get the column names
	function get_columns(){
		$columns = array(
				'cb' => '<input type="checkbox" />',
				'keyword' => __('Keyword'),
				'priority' => __('Priority'),
				'status' => __('Status'),
			);
	
		return $columns;
	}
	
	//make some column sortable
	function get_sortable_columns(){
		$sortable_columns = array(
				'keyword' => array('keyword', false),
				'priority' => array('priority', false)
		);
	
		return $sortable_columns;
	}
	
	
	//pagination
	private function _set_pagination_parameters(){
			
		$this->current_page = $this->get_pagenum(); //it comes form mother class (WP_List_Table)
	
		$this->total_items = $this->KwDb->get_total_keywords($_REQUEST['s']);
		$this->per_page = 25;
	
		$this->set_pagination_args( array(
				'total_items' => $this->total_items,                  //WE have to calculate the total number of items
				'per_page'    => $this->per_page,                     //WE have to determine how many items to show on a page
				'total_pages' => ceil($this->total_items/$this->per_page)   //WE have to calculate the total number of pages
		) );
	
	}
	
	
	//collectes every information
	function populate_table_data(){
		$kw_table = $this->KwDb->get_keyword_table();
		
		$sql = "select * from $kw_table";
		
		if(isset($_REQUEST['s']) && !empty($_REQUEST['s'])){
			$s = trim($_REQUEST['s']);
			$sql .= " where keyword like '%$s%' or priority like '%$s%'";
		}
		
		//order
		$order_by = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'keyword';
		$order = (isset($_GET['order'])) ? $_GET['order'] : 'asc';
		$sql .= " order by $order_by $order";
		
		//pagination
		$current_page = ($this->current_page > 0) ? $this->current_page - 1 : 0;
		$offset = (int) $current_page * (int) $this->per_page;
		$sql .= " limit $this->per_page offset $offset";
		
		$keywords = $this->KwDb->db->get_results($sql);
		$sanitized_keywords = array();
		
		if($keywords){
			foreach($keywords as $keyword){
				
				$is_used = $this->KwDb->is_used($keyword->id);
				
				$sanitized_keywords[] = array(
					'id' => $keyword->id,
					'keyword' => $keyword->keyword,
					'priority' => $keyword->priority,
					'status' => $is_used ? "Used" : "Not Used",
				);
			}
		}
		
		return $sanitized_keywords;
	}
	
	
	/* Utility that are mendatory   */
	
	/* checkbox for bulk action*/
	function column_cb($item) {
		return sprintf(
				'<input type="checkbox" name="keyword_id[]" value="%s" />', $item['id']
		);
	}
	
	
	/* default column checking and it is must */
	function column_default($item, $column_name){
		switch($column_name){
			case "id":
			case "keyword":
			case "priority":
			case "status":
			return $item[$column_name];
				break;
			default:
				var_dump($item);
					
		}
	}
	
	
	/*adding extra actions when hovering first column
	 * name is column name
	 *  */
	function column_keyword($item){
	
		$delete_href = sprintf('?page=%s&action=%s&keyword_id=%s', $_REQUEST['page'],'delete',$item['id']);
	
		if(isset($_REQUEST['s']) && !empty($_REQUEST['s'])){
			$delete_href = add_query_arg(array('s'=>$_REQUEST['s']), $delete_href);
		}
	
		if($this->get_pagenum()){
			$delete_href = add_query_arg(array('paged'=>$this->get_pagenum()), $delete_href);
		}
	
		$actions = array(
				'edit' => sprintf('<a href="?page=%s&action=%s&keyword_id=%s">Edit</a>','addnew_keyword','edit',$item['id']),
				'delete' => "<a href='$delete_href'>Delete</a>"
		);
	
	
		return sprintf('%1$s %2$s', $item['keyword'], $this->row_actions($actions) );
	}
	
	
	//bulk actions initialization
	function get_bulk_actions() {
		$actions = array(
				'delete'    => 'Delete'
		);
		return $actions;
	}
	
	
	//handle bulk action
	function handle_bulk_action(){
		
		//var_dump($_REQUEST['keyword_id']);
		
		$message = 0;
		if($this->current_action() == 'delete'){
			$keyword_ids = $_REQUEST['keyword_id'];
		
			if(!is_array($keyword_ids)){
				$keyword_ids = array($keyword_ids);
			}
			
			foreach($keyword_ids as $keyword_id){
				$this->KwDb->delete_keyword($keyword_id);
			}
			
			$message = count($keyword_ids);			
		}
		
		return $message;
	}
	
}