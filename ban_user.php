<?php
	include("jp_library/jp_lib.php");
	
	if(isset($_GET['uid'])){
		$data = array('disabled' => 1)
		$search['table'] = "veeds_users";
		$search['data'] = $data;
		$search['where'] = "user_id = '".$_GET['uid']."'"; 
			
		if(jp_update($search))
?>
	<script type="text/javascript">
		alert("User account has been disabled.");
	</script>
<?php		
	}
?>