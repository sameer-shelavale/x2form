<?php
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


$formObj = new \X2Form\Form( 'MyForm', [] );
$formObj->attributes->method = "POST";
$formObj->attributes->action = 'sample1_process.php';

$formObj->elements->FIRST_NAME = new \X2Form\Element(
    'text',
    array(
        'name'=>'FIRST_NAME',
        'label'=>'First Name'
    )
);

$formObj->elements->LAST_NAME = new \X2Form\Element(
    'text',
    array(
        'name'=>'LAST_NAME',
        'label'=>'Last Name',
        'value'=>'Potter'
    )
);

$formObj->elements->QMAIL = new \X2Form\Element(
    'text',
    array(
        'name'=>'QMAIL',
		'label'=>'Email',
		'mandatory'=>'true',
		'datatype'=>'email'
    )
);

$formObj->elements->PROFESSION = new \X2Form\Element(
    'dropdown',
    array(
        'name'=>'PROFESSION',
		'label'=>'Profession',
		'mandatory'=>'true',
		'description'=>"You need not apply if your profession is not listed here.",
		'datatype'=>'email',
		'options'=>array(
            array( 'value'=>'', 'label'=>'Select One' ),
			array( 'value'=>'seeker', 'label'=>'Seeker' ),
			array( 'value'=>'keeper', 'label'=>'Keeper' ),
			array( 'value'=>'blindsidebeater', 'label'=>'Blind Side Beater' ),
			array( 'value'=>'opensidebeater', 'label'=>'Open Side Beater' ),
			array( 'value'=>'centerchaser', 'label'=>'Center Chaser' ),
			array( 'value'=>'outsidechaser', 'label'=>'Out Side Chaser' )
		)
    )
);

$formObj->elements->BROOMSTICKS = new \X2Form\Element(
    'checkbox',
    array(
        'name'=>'BROOMSTICKS',
		'label'=>'Select the broomsticks you have',
		'direction'=>'vertical',
		'options'=>array(
            array( 'value'=>'cleansweep', 'label'=>'Cleansweep' ),
			array( 'value'=>'nimbus', 'label'=>'Nimbus' ),
            array( 'value'=>'nimbus2000', 'label'=>'Nimbus 2000' ),
            array( 'value'=>'nimbus2001', 'label'=>'Nimbus 2001' ),
            array( 'value'=>'firebolt', 'label'=>'Firebolt' )
        )
    )
);
																						
$formObj->elements->RECEIVE_OFFERS = new \X2Form\Element(
    'checkbox',
    array(
        'name'=>'RECEIVE_OFFERS',
		'label'=>'Receive Special Offers?',
		'direction'=>'vertical',
		'options'=>array(
            array( 'value'=>'yes', 'label'=>'Yes' )
        )
    )
);

$formObj->elements->MATCHES_PLAYED = new \X2Form\Element(
    'radio',
    array(
        'name'=>'MATCHES_PLAYED',
		'label'=>'How many matches have you played?',
		'direction'=>'vertical',
		'options'=>array(
            array( 'value'=>'0', 'label'=>'0' ),
            array( 'value'=>'1-5', 'label'=>'1 to 5' ),
            array( 'value'=>'6-10', 'label'=>'6 to 10' ),
            array( 'value'=>'11-25', 'label'=>'11 to 25' ),
            array( 'value'=>'26-100', 'label'=>'26 to 100' ),
            array( 'value'=>'100+', 'label'=>'more than 100' )
        )
    )
);
																						
$formObj->elements->HOUSE = new \X2Form\Element(
    'dropdown',
    array(
        'name'=>'HOUSE',
        'label'=>'Which house you are from?',
        'direction'=>'vertical',
        'framework'=>'php',
        'prompt'=>'Select your house',
        'options'=>array(
            'query'=>'SELECT * FROM houses;',
            'valuefield'=>'HOUSE_ID',
            'labelfield'=>'NAME'
        )
    )
);

$formObj->elements->IS_SEPARABLE = new \X2Form\Element(
    'radio',
    array(
        'name'=>'IS_SEPARABLE',
        'label'=>'How many matches have you played?',
        'direction'=>'horizontal',
        'options'=>array(
            array( 'value'=>'True', 'label'=>'Yes' ),
            array( 'value'=>'False', 'label'=>'No' )
        )
    )
);

$formObj->elements->INTRO = new \X2Form\Element(
    'textarea',
    array(
        'name'=>'INTRO',
        'label'=>'Write something about yourself',
        'rows'=>'5',
        'cols'=>'50',
        'value'=>'I am Harry... Harry Potter.'
    )
);

$formObj->elements->PHOTO = new \X2Form\Element(
    'file',
    array(
        'name'=>'PHOTO',
        'label'=>'Upload your photo',
        'maxsize'=>'2',
        'allowextensions'=>'jpg,png,jpeg',
        'iffileexists'=>'renamenew',
        'uploaddirectory'=>'uploaded_files/'
    )
);

$formObj->elements->PORTFOLIO = new \X2Form\Element(
    'file', array(
        'name'=>'PORTFOLIO',
        'label'=>'Upload your portfolio',
        'description'=>'Only in pdf format',
        'mandatory'=>'true',
        'maxsize'=>'2',
        'allowextensions'=>'pdf',
        'iffileexists'=>'renamenew',
        'uploaddirectory'=>'uploaded_files/'
    )
);

$formObj->elements->LOT = new \X2Form\Element(
    'hidden',
    array(
        'name'=>'LOT',
		'value'=>'sds8sds785fdf'
	)
);

$formObj->elements->submit = new \X2Form\Element(
    'submit',
    array(
        'name'=>'submit',
		'value'=>'Submit',
		'class'=>"siteButtons saveDiamondButton"
    )
);
																 
$formObj->elements->dump = new \X2Form\Element(
    'submit',
    array(
        'name'=>'dump',
        'value'=>'Submit & dump data',
        'class'=>"siteButtons saveDiamondButton"
    )
);

$formObj->elements->eventbutton = new \X2Form\Element(
    'button',
    array(
        'name'=>'eventbutton',
        'value'=>'Click event',
        'events'=> array( 'onclick'=>"alert('This click event originates from XML definition of this form!')" )
    )
);

$formObj->elements->CAPTCHA = new \X2Form\Element(
    'captcha',
    array(
        'secret'=>'secret-code-for-this-form',
        'refreshUrl'=>'sample2-no-xml.php',
        'options'=> array(
            'math'=>[
                'level'=>'4'
            ],
            'gif' => [
                'maxCodeLength' => 6,
                'width'=>180,
                'height'=>60,
                'totalFrames'=>50,
                'delay'=>20
            ]
        ),
        'refreshUrl'=>'sample2-no-xml.php?captcha=refresh',
        'helpUrl'=>'http://github.com/sameer-shelavale/multi-captcha'
    )
);

$formObj->finalize();

if( isset( $_REQUEST['captcha'] ) && $_REQUEST['captcha'] == 'refresh' ){
    echo $formObj->renderer->elementRenderer->refreshCaptcha( $formObj->elements['captcha'] );
    exit;
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<title>Constructing form using only php(without using any xml)</title>
	<link href="style.css" type="text/css" rel="stylesheet">
</head>
<body>
	<div id="container">
		<h1>Example 1: X2Form Form Generator</h1>
		<?php echo $formObj->render(); ?>
	</div>
	<br/>
	<br/>
</body>
</html>
