<?php
require_once( '../src/form.php' );


$link = mysql_connect('localhost', 'root', '');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
$db_selected = mysql_select_db('test', $link);
if (!$db_selected) {
    die ('Can\'t select database : ' . mysql_error());
}


//FIRST_NAME, LAST_NAME and PROFESSION is populated from xml
$formObj = new \X2Form\Form( 'DiamondForm', ['from'=> 'forms/quidich_form_grouped.xml'] );

//print_r( $formObj->elements );

$formObj->elements['FAMILY_MEMBERS']->setValues( array( 0=>array( 'FIRST_NAME'=>'sameer' ), 1=>array( 'LAST_NAME'=>'test' ) ) );


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<title>Displaying a Form with collection of fields</title>
	<link href="style.css" type="text/css" rel="stylesheet">
	<link href="js/jquery-ui-1.9.2.custom/css/smoothness/jquery-ui-1.9.2.custom.css" type="text/css" rel="stylesheet">
	<script type="text/javascript" src="js/jquery-ui-1.9.2.custom/js/jquery-1.8.3.min.js" ></script>
	<script type="text/javascript" src="js/jquery-ui-1.9.2.custom/js/jquery-ui-1.9.2.custom.min.js" ></script>
	
</head>
<body>
	<div id="container">
		<?php echo $formObj->render(); ?>
	</div>
	<br/>
	<br/>
</body>
</html>
