<?php
require_once( '../src/form.php' );
require_once( '../vendor/sameer-shelavale/multi-captcha/src/Captcha.php' );
require_once( '../vendor/autoload.php' );

$link = mysql_connect('localhost', 'root', '');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
$db_selected = mysql_select_db('test', $link);
if (!$db_selected) {
    die ('Can\'t select database : ' . mysql_error());
}


//FIRST_NAME, LAST_NAME and PROFESSION is populated from xml
$formObj = new \X2Form\Form(
    'DiamondForm',
    [
        'from'=> 'forms/quidich_form.xml',
        'renderer' => new X2Form\Renderers\Bootstrap\Renderer()
    ]
);
$formObj->elements['captcha']->config['refreshurl'] = 'sample1-bootstrap.php?captcha=refresh';
$formObj->finalize();
if( isset( $_POST['submit'] ) &&  $_POST['submit'] == "Submit" ){

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
	<title>Displaying Sample 1 using bootstrap</title>

    <link rel="stylesheet" href="../vendor/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/bootstrap/dist/css/bootstrap-theme.min.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
</head>
<body>
	<div class="container">
		<?php echo $formObj->render(); ?>
	</div>
	<br/>
	<br/>
</body>
</html>
