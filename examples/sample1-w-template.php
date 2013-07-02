<?php
require_once( '../X2Form.php' );
require_once( '../X2FormElement.php' );
require_once( '../X2FormCollection.php' );
require_once( '../class.dbhelper.php' );
require_once( '../class.logger.php' );

$link = mysql_connect('localhost', 'root', '');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
$db_selected = mysql_select_db('test', $link);
if (!$db_selected) {
    die ('Can\'t select database : ' . mysql_error());
}

//FIRST_NAME, LAST_NAME and PROFESSION is populated from xml
$formObj = new X2Form( 'DiamondForm', 'xmlfile', 'forms/quidich_form_populated.xml', 'forms/template1.html'  );

//we can also populate fieds from php using setFormValues() method of the X2Form class 
$values = array('BROOMSTICKS'=>array('nimbus2001', 'firebolt'),
				'QMAIL'=>'harry@hogwarts.com',
				'MATCHES_PLAYED'=>'11-25' );

$formObj->setValues( $values );


//Alternatively we can also populate individual fields directly
$formObj->elements['HOUSE']->value = '1';	//arary syntax 
$formObj->elements->INTRO->value = "I am a Harry... Harry Potter!"; //this syntax works as well


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<title>Diplaying a Form using X2Form</title>
	<link href="style.css" type="text/css" rel="stylesheet">
</head>
<body>
	<div id="container">
		<h1>Example 1: X2Form Form Generator</h1>
		<h3>Displaying form stored in <a href="forms/quidich_form.xml">xml file</a></h3>
		<?php echo $formObj->render(); ?>
	</div>
	<br/>
	<br/>
</body>
</html>
