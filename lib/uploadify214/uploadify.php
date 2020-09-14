<?php
if ( !empty($_FILES) ) {	
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$date = date('YmdGis');

	if(!file_exists( $_POST['fullpath'] . '/'. $_POST['path']) ) mkdir( $_POST['fullpath'] . '/'. $_POST['path'], 0777, true);
	$path = $_POST['fullpath'] . '/'. $_POST['path'] .'/' . $date . '_' . str_replace(' ', '_', $_FILES['Filedata']['name']);
	
	//remove file if exists, overwrite
	if(file_exists($path)) unlink($path);
	
	move_uploaded_file( $tempFile, $path );
	
	$finfo = pathinfo( $path );

	//check if upload an image
	if( in_array( $finfo['extension'], array('jpeg', 'jpg', 'JPEG', 'JPG', 'gif', 'GIF', 'png', 'PNG', 'bmp', 'BMP') ) ){
		@$response->file_type = 'image';
		$size = getimagesize( $path );
		
		//if with greater than 800
		if( $size[0] > 1024 || $size[1] > 768){
			switch($finfo['extension']){
				case 'jpeg':
					$src_img = @imagecreatefromjpeg( $path );
					break;
				case 'JPEG':
					$src_img = @imagecreatefromjpeg( $path );
					break;
				case 'jpg':
					$src_img = @imagecreatefromjpeg( $path );
					break;
				case 'JPG':
					$src_img = @imagecreatefromjpeg( $path );
					break;	
				case 'gif':
					$src_img = @imagecreatefromjpeg( $path );
					break;
				case 'GIF':
					$src_img = @imagecreatefromjpeg( $path );
					break;
				case 'png':
					$src_img = @imagecreatefrompng( $path );
					break;
				case 'PNG':
					$src_img = @imagecreatefrompng( $path );
					break;
				default:
					$src_img = @imagecreatefromjpeg( $path );
			}
			
			if($size[0] >= $size[1]){
				$new_width = 1024;
				$percentage_change = 1024/$size[0];
				$new_height = $size[1] * $percentage_change;
			}
			
			if($size[0] < $size[1]){
				$new_height = 768;
				$percentage_change = 768/$size[1];
				$new_width = $size[0] * $percentage_change;
			}
			
			$to_resample = ImageCreateTrueColor( $new_width, $new_height );
			imagecopyresampled($to_resample , $src_img, 0, 0, 0, 0, $new_width,$new_height,$size[0],$size[1]); 
			
			switch($finfo['extension']){
				case 'jpeg':
					imagejpeg( $to_resample, $path ); 
					break;
				case 'JPEG':
					imagejpeg( $to_resample, $path ); 
					break;
				case 'jpg':
					imagejpeg( $to_resample, $path ); 
					break;
				case 'JPG':
					imagejpeg( $to_resample, $path ); 
					break;
				case 'gif':
					imagejpeg( $to_resample, $path ); 
					break;
				case 'GIF':
					imagejpeg( $to_resample, $path ); 
					break;
				case 'png':
					imagepng( $to_resample, $path ); 
					break;
				case 'PNG':
					imagepng( $to_resample, $path ); 
					break;
				default:
					imagejpeg( $to_resample, $path );
			}
			
			imagedestroy($to_resample); 
			imagedestroy($src_img); 
		}
	} else {
		@$response->file_type = 'document';
	}

	$path = pathinfo($_FILES['Filedata']['name']);

	echo $_POST['path'] .'/' . $date . '_' . str_replace(' ', '_', $_FILES['Filedata']['name']);
}

?>