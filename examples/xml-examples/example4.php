<?php

require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

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
        'language' => 'marathi', // here we set the language to be used
        'renderer' => new X2Form\Renderers\Bootstrap\Renderer() //IMP: set Bootstrap renderer
    )
);
$formObj->attributes['action'] = 'example3.php';
//$formObj->elements['FAMILY_MEMBERS']->setValues( array( 0=>array( 'FIRST_NAME'=>'sameer' ), 1=>array( 'LAST_NAME'=>'test' ) ) );
$formObj->finalize();
if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" ){
    $log = $formObj->processSubmission( $_POST );
    if( !logg_ok( $log ) ){
        $message = '<div class="error">'. logg_msg( $log ).'</div>';
        $form->rollBackFileUploads();
    }else{
        $message = '<div class="success">'. logg_msg( $log ).'</div>';
    }
}elseif( isset( $_REQUEST['captcha'] ) && $_REQUEST['captcha'] == 'refresh' ){
    echo $formObj->renderer->elementRenderer->refreshCaptcha( $formObj->elements['captcha'] );
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Example 4: Multiple language support</title>

    <!-- HERE WE INCLUDE BOOTSTRAP CSS -->
    <link rel="stylesheet" href="../../vendor/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../vendor/bootstrap/dist/css/bootstrap-theme.min.css">

    <!-- AND a supplimentary stylesheet to make some items look good -->
    <link rel="stylesheet" href="../css/x2form-bootstrap.css">
    <!-- include jquery as some things like collections and captcha need it -->
    <script type="text/javascript" src="../js/jquery-ui-1.9.2.custom/js/jquery-1.8.3.js" ></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <h1>Example 4</h1>
            <h3>Multi language support</h3>

            <div class="row">
                <div class="col-md-12">
                    <?php
                    //lets print the submitted data if the validation was successful
                    if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" ){
                        ?>
                        <h2>Posted values</h2>
                        <?php var_dump( $_POST ); ?>
                    <?php } ?>
                </div>

            </div>

        </div>
        <div class="col-md-6 col-sm-12">


            <h2>Sample Quidich Tournament Application Form</h2>
            <?php
            if( isset( $message) && $message ){
                echo $message;
            }
            echo $formObj->render(); ?>

            <?php
            //lets print the submitted data if the validation was successful
            if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" && logg_ok( $log ) ){
                ?>
                <div class="container">
                    <h2>Submited values</h2>
                    <?php var_dump( $form->getValues() ); ?>
                </div>
            <?php } ?>
        </div>
    </div>

</body>
</html>
