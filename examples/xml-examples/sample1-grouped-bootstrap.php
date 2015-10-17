<?php
require_once('../src/autoload.php');
require_once('../vendor/autoload.php');

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
        'from'=> 'forms/quidich_form_grouped.xml',
        'renderer' => new X2Form\Renderers\Bootstrap\Renderer()
    ]
);

//print_r( $formObj->elements );

$formObj->elements['FAMILY_MEMBERS']->setValues( array( 0=>array( 'FIRST_NAME'=>'sameer' ), 1=>array( 'LAST_NAME'=>'test' ) ) );
$formObj->finalize();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<title>Displaying a Form using bootstrap</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">

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
