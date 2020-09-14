	<?php
		$data = unserialize(base64_decode($_GET['data']));
		$userinfo = $data['userinfo'];
		if(isset($data['user_access'])) $user_access = $data['user_access'];
		$module_id = $data['module_id'];
		$module = $data['module'];
		$idle_time = $data['idle_time'];
		$base_url = $data['base_url'];
		$fcpath = $data['fcpath'];
		$module_link = $data['module_link'];
		$method = $data['method'];
		$record_id = $data['record_id'];
		$client_no = $data['client_no'];
	?>

	function User(){
		var user_id = "<?php echo isset($userinfo['user_id']) ? $userinfo['user_id'] : ''?>";
		var nicename = "<?php echo isset($userinfo['user_id']) ? $userinfo['firstname'].' '.$userinfo['lastname'] : 'Anonymous'?>";
		var avatar = "<?php echo isset($userinfo['photo']) ? $userinfo['photo'] : ''?>";
		var user_theme = "<?php echo $userinfo['theme']?>";
		var birth_date = "<?php echo isset($userinfo['birth_date']) ? $userinfo['birth_date'] : ''?>";
		var idle_time = "<?php echo ($idle_time)?>"; <?php
		if($module != 'login' && $module != 'design' && $module != 'development' && isset( $user_access )){
			foreach($user_access as $action => $value){
				echo "\r\n".'var '. $action .'_control = "'. $value .'";';
			} 				
		}
		echo "\r\n";?>
		this.get_value = function( to_get ){
			if( eval( to_get ) == undefined )
				return false;
			else
				return eval( to_get );
		};
	}
	var user = new User();
	
	function Module(){
		var base_url = "<?php echo $base_url?>";
		var fullpath = "<?php echo str_replace("\\", "\\\\", $fcpath)?>";
		var module_id = "<?php echo $module_id?>";
		var module_link = "<?php echo $module_link?>";
		var view = "<?php echo $method?>";
		var record_id = "<?php echo $record_id?>";
		var client_no = "<?php echo $client_no?>";
		this.get_value = function( to_get ){
			if( eval( to_get ) == undefined )
				return false;
			else
				return eval( to_get );
		};

		this.set_value = function ( to_set, value ){
			if( eval( to_set ) == undefined ){
				return false;
			}
			else{
				eval( to_set + ' = ' + value );	
				return true;
			}
		}

	}			
	var module = new Module();
	var view = "<?php echo $method?>";
