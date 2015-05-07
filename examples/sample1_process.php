<?php

require_once( '../X2Form.php' );

$link = mysql_connect('localhost', 'root', '');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
$db_selected = mysql_select_db('test', $link);
if (!$db_selected) {
    die ('Can\'t select database : ' . mysql_error());
}


$formObj = new \X2Form\Form(
    'DiamondForm', ['file'=> 'forms/quidich_form.xml']  );

if( $_POST['submit'] == "Submit" ){
	
	$formObj->setValues($_POST);
	$log = $formObj->processSubmission( $_POST );	
	if( $log['result'] != 'Success' ){
		$formObj->rollBackFileUploads();
	}
	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<title>Processing a Form using X2Form</title>
	<link href="style.css" type="text/css" rel="stylesheet">
</head>
<body>
	<div id="container">
		<h1>Example 1: X2Form Form Generator</h1>
		<h3>Displaying form stored in <a href="forms/quidich_form.xml">xml file</a></h3>
		<?php 
		
		if( $_POST['dump'] ){
			var_dump( $_POST );
		}elseif( $result['result'] == 'Success'){
			var_dump( $result );
			
		}else{
			echo '<div class="error">'.$result['message'].'<br/>'.$formObj->errorString."</div>";
			echo $formObj->render();
		}
		 
		?>
	</div>
	<br/>
	<br/>
</body>
</html>
