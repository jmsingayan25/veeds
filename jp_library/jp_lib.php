<?php
/*
Name: Jp Library
Version: 4
Author: John Patrick S. Ang
Description: A PHP Database and File Upload utility library. 
Site: http://murakami-night.blogspot.com/
*/
function jp_create_data($data, $type){
	$data_string = "";
	
	foreach($data as $datum)
		if(empty($data_string)){
			if($type == "field")
				$data_string .= $datum;
			elseif($type == "value")
				$data_string .= "'".addslashes($datum)."'";
		}else{
			if($type == "field")
				$data_string .= ", ".$datum;
			elseif($type == "value")
				$data_string .= ", '".addslashes($datum)."'";
		}	
	return $data_string;
}
	
function jp_upload($image_data, $type, $path){
	$extension = explode(".", $image_data["name"]);	
	$extension = end($extension);
	$extension = strtolower($extension);
	
	if(!file_exists($path))
		mkdir($path, 0777, true);	
	
	$filename = "$type".time().".".$extension;
		
	$filepath = "$path/".$filename;
			
	move_uploaded_file($image_data["tmp_name"], $filepath);
		
	return $filename;
}

function jp_pagination($current_page, $total_pages, $pagination_count){
	$split = ceil($pagination_count/2);
	
	$pagination = array(); 
	
	if($total_pages <= $pagination_count)
		for($i=1; $i<=$total_pages; $i++)
			$pagination[] = $i;
	elseif($current_page <= $split)
		for($i=1; $i<=$pagination_count; $i++)
			$pagination[] = $i;
	elseif($current_page >= $total_pages-($split-1))
		for($i=$total_pages-($pagination_count-1); $i<=$total_pages; $i++)
			$pagination[] = $i;
	else
		for($i=$current_page-($split-1); $i<=$current_page+($split-1); $i++)
			$pagination[] = $i;

	return $pagination;
}

function jp_add($data){
	return $GLOBALS['con']->jp_add($data);
}

function jp_last_added(){
	return $GLOBALS['con']->jp_last_added();
}

function jp_get($data){
	return $GLOBALS['con']->jp_get($data);
}

function jp_delete($data){
	return $GLOBALS['con']->jp_delete($data);
}

function jp_count($data){
	return $GLOBALS['con']->jp_count($data);
}

function jp_update($data){
	return $GLOBALS['con']->jp_update($data);
}

function jp_query($query){
	return $GLOBALS['con']->con->query($query);
}

function jp_union($data){
	return $GLOBALS['con']->jp_union($data);
}

function jp_escape($data){
	return $GLOBALS['con']->jp_escape($data);
}

class jp_controller{
	public $con;
	protected $db;
	
	function __construct($host, $user, $pass, $database) {
        $this->db = $database;
		$this->con = new mysqli($host, $user, $pass, $database);
    }
	
	public function jp_add($params){
		$table = $params['table'];
		$data = $params['data'];
		if(isset($params['debug']))
			$debug = $params['debug'];
		else
			$debug = 0;
			
		$columns = $this->jp_show_column($table);
		$table_fields = array();
		
		while($row = mysqli_fetch_assoc($columns))
			$table_fields[] = $row['COLUMN_NAME']; 
		
		foreach(array_keys($data) as $table_field)
			if(!in_array($table_field, $table_fields, true))
				unset($data[$table_field]);
		
		$fields = jp_create_data(array_keys($data),'field');
		$values = jp_create_data($data,'value');
		
		$query = "INSERT INTO $table ($fields) VALUES ($values)";
		
		if($debug == 1)
			echo $query;
		elseif($this->con->query($query))
			return true;
		else
			return false;
	}
		
	protected function jp_show_column($table){
		$result = $this->con->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$this->db."' AND TABLE_NAME = '$table';");
		//echo "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$this->db."' AND TABLE_NAME = '$table';";
		return $result;	
	}
	
	protected function jp_get_primary($table){
		$result = $this->con->query("SELECT k.column_name FROM information_schema.table_constraints t JOIN information_schema.key_column_usage k USING(constraint_name,table_schema,table_name) WHERE t.constraint_type='PRIMARY KEY' AND t.table_schema='".$this->db."' AND t.table_name='$table'");
		$field = mysqli_fetch_array($result);
		if(!empty($field['column_name']))
			return $field['column_name'];
		else
			return "*";	
	}
	
	public function jp_last_added(){
		return $this->con->insert_id;	
	}
		
	public function jp_count($params){ 
		$table = $params['table'];
		
		if(isset($params['select']))
			$field = $params['select'];
		else
			$field = $this->jp_get_primary($table);
		
		if(isset($params['where']))
			$where = $params['where'];
			
		if(isset($params['filters']))
			$filters = $params['filters'];
		else
			$filters = "";
		
		if(isset($params['debug']))
			$debug = $params['debug'];
		else
			$debug = 0;
		
		if(!isset($where))
			$query = "SELECT $field FROM $table $filters";
		else
			$query = "SELECT $field FROM $table WHERE $where $filters";
		
		if($debug == 0){		
			$result = $this->con->query($query);
			if(!empty($result))
				return mysqli_num_rows($result);
			else
				return 0;
		}else
			echo $query;
	}
	
	public function jp_get($params){
		if(isset($params['select']))
			$select = $params['select'];
		else
			$select = "*";
			
		$table = $params['table'];
		
		if(isset($params['where']))
			$where = $params['where'];
			
		if(isset($params['filters']))
			$filters = $params['filters'];
		else
			$filters = "";
			
		if(isset($params['debug']))
			$debug = $params['debug'];
		else
			$debug = 0;
			
		if(!isset($where))
			$query = "SELECT $select FROM $table $filters";
		else
			$query = "SELECT $select FROM $table WHERE $where $filters";
			
		if($debug == 0){		
			$result = $this->con->query($query);
			
			return $result;	
		}else
			echo $query;
	}
	
	public function jp_delete($params){
		$table = $params['table'];
		
		if(isset($params['where']))
			$where = $params['where'];
		else
			$where = "";
			
		if(isset($params['debug']))
			$debug = $params['debug'];
		else
			$debug = 0;
			
		$query = "DELETE FROM $table WHERE $where";
		
		if($debug == 1)
			echo $query;	
		elseif(!empty($where) && $this->con->query($query))
			return true;
		else
			return false;
	}
	
	public function jp_union($params){ 
		$queries = $params['queries'];
		
		if(isset($params['filters']))
			$filters = $params['filters'];
		else
			$filters = "";
			
		if(isset($params['all']))
			$all = "ALL";
		else
			$all = "";
			
		if(isset($params['debug']))
			$debug = $params['debug'];
		else
			$debug = 0;
	
		if(count($queries) < 2)
			return false;
		else{
			$query = "";
			foreach($queries as $query_string)
				if($query == "")
					$query .= $query_string;
				else
					$query .= " UNION ".$all." ".$query_string;
				
			$query .= " ".$filters;
			if($debug == 1)
				echo $query;	
			elseif($debug == 0){		
				$result = $this->con->query($query);
				return $result;	
			}
		}
	}
	
	public function jp_update($params){ 
		$table = $params['table'];
		$data = $params['data'];
		
		if(isset($params['where']))
			$where = $params['where'];
		else
			$where = "";
			
		if(isset($params['debug']))
			$debug = $params['debug'];
		else
			$debug = 0;
		
		$columns = $this->jp_show_column($table);
		$table_fields = array();
		
		while($row = mysqli_fetch_assoc($columns))
			$table_fields[] = $row['COLUMN_NAME']; 
		
		foreach(array_keys($data) as $table_field)
			if(!in_array($table_field, $table_fields, true))
				unset($data[$table_field]);
	
		$data_string = "";
		
		foreach(array_keys($data) as $datum)
			if(empty($data_string))
				$data_string .= $datum." = '".addslashes($data[$datum])."'";
			else
				$data_string .= ", ".$datum." = '".addslashes($data[$datum])."'";
		
		$query = "UPDATE $table SET $data_string WHERE $where";
		
		if($debug == 1)
			echo $query;	
		elseif(!empty($where) && $this->con->query($query))
			return true;
		else
			return false;
	}

	public function jp_escape($params){

		$result = $this->con->real_escape_string($params);

		return $result;
	}
}	

	date_default_timezone_set('Asia/Manila');


	// $con = new jp_controller('localhost','veeds_user','!O6y8q38zx>~kJL','veeds');
	$con = new jp_controller('localhost','root','','veeds2');
?>