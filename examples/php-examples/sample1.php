<?php
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'sample1.php',
        'method' => 'post',
        'elements' => [
            [
                'type' => 'text',
                'name' => 'NAME',
                'label' => 'Your Name',
                'mandatory' => true,
            ],
            [
                'type' => 'text',
                'name'  =>  'EMAIL',
                'label' =>  'Your Email',
                'mandatory' => true,
                'datatype'  => 'email'
            ],
            [
                'type'=>'textarea',
                'name'=>'MESSAGE',
                'label'=>'Message',
                'rows'=>'4',
                'cols'=>'50',
                'mandatory' => true
            ],
            [
                'type'=>'captcha',
                'name'=>'CAPTCHA',
                'secret' => 'blahblah'
            ],
            [
                'type'=>'submit',
                'name'=>'submit',
                'value'=>'Submit'
            ]
        ]
    ]
);

$form->finalize();

//handle the form submission
if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" ){

    $form->setValues( $_POST );
    $log = $form->processSubmission( $_POST );
    if( !logg_ok( $log ) ){
        $message = '<div class="error">'. logg_msg( $log ).'</div>';
        $form->rollBackFileUploads();
    }else{
        $message = '<div class="success">'. logg_msg( $log ).'</div>';

    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<title>Constructing form using only php(without using any xml)</title>
	<link href="../css/style.css" type="text/css" rel="stylesheet">
</head>
<body>
	<div id="container">
		<h1>Example 1</h1>
        <h2>Constructing a simple contact us form using only PHP calls(using constructor params to add elements)</h2>
        <?php
        if( isset( $message) && $message ){
            echo $message;
        }
        echo $form->render(); ?>
	</div>
    <?php
    //lets print the submitted data if the validation was successful
    if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" && logg_ok( $log ) ){
    ?>
	<div id="container">
        <h2>Submited values</h2>
        <?php var_dump( $form->getValues() ); ?>
    </div>
    <?php } ?>
</body>
</html>
