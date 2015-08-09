<?php
use \X2form\Form;
require_once( '../src/autoload.php' );
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
$formObj = new Form(
    'QuidichForm',
    ['from'=> 'forms/quidich_form.xml']
);
$formObj->finalize();
if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" ){

    $formObj->setValues($_POST);
    $log = $formObj->processSubmission( $_POST );
    if( $log['result'] != 'Success' ){
        $formObj->rollBackFileUploads();
    }

}elseif( isset( $_REQUEST['captcha'] ) && $_REQUEST['captcha'] == 'refresh' ){
    echo $formObj->renderer->elementRenderer->refreshCaptcha( $formObj->elements['captcha'] );
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<title>A simple HTML form using X2Form</title>
	<link href="style.css" type="text/css" rel="stylesheet">
</head>
<body>
	<div id="container">
		<h2>Quidich Form</h2>
		<div id="innerContainer">
			<?php echo $formObj->render(); ?>
		</div>
	</div>
	<br/>
	<br/>
</body>
</html>