<?php
/*
UploadiFive
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
*/

// connect to the database
include "../db/db.php";

// Set the uplaod directory
$uploadDir = '/user_files/'.$_POST['user_id'].'/';

if(!is_dir($_SERVER['DOCUMENT_ROOT'].$uploadDir))
{
	mkdir($_SERVER['DOCUMENT_ROOT'].$uploadDir, 0777);
	chmod($_SERVER['DOCUMENT_ROOT'].$uploadDir, 0755);
}

// Set the allowed file extensions
$fileTypes = array('jpg', 'jpeg', 'gif', 'png', 'mp4', 'ogv', 'webm'); // Allowed file extensions

$verifyToken = md5('unique_salt' . $_POST['timestamp']);

if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
	$tempFile   = $_FILES['Filedata']['tmp_name'];
	$uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
	$targetFile = $uploadDir . $_FILES['Filedata']['name'];

	// Validate the filetype
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
		// Save the file
		move_uploaded_file($tempFile, $targetFile);

		// db stuff
			$name_bits=explode(".",$_FILES['Filedata']['name']);
			mysql_query("insert into node (name,type,user_id) values ('".$name_bits[0]."','video',".$_POST['user_id'].")");
			$id=mysql_insert_id();
			mysql_query("insert into video (node_id,video_path) values (".$id.",'".$targetFile."')");

		//header('location:/video/list');

	} else {
		// The file type wasn't allowed
		echo 'Invalid file type.';

	}
}
?>