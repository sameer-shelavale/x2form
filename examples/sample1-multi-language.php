<?php

require_once( '../src/form.php' );
require_once( '../vendor/autoload.php' );

$link = mysql_connect('localhost', 'root', '');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
$db_selected = mysql_select_db('test', $link);
if (!$db_selected) {
    die ('Can\'t select database : ' . mysql_error());
}

//create the X2Form object from XML file
$formObj = new \X2Form\Form(
    'QuidichForm',
    array(
        'from'=>'forms/quidich_form_multi_language.xml',
        'language' => 'marathi'
    )
);
$formObj->finalize();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>A simple HTML with multilanguage supportform using X2Form </title>
	<link href="style.css" type="text/css" rel="stylesheet">
</head>
<body>
	<div id="container">
		<h2>Quidich Form (Multi language)</h2>
		<div id="innerContainer">
			<?php echo $formObj->render(); ?>
		</div>
	</div>
	<br/>
	<br/>
</body>
</html>