<?php
/*
 * 3. displaying form using bootstrap
 * 4. templates - customizing the layout
 * 5. collection
 * 6. group
 * 7. populating values
 * 8. Creating form from db table
 * 9. Creating form directly from Laravel Models(Elequent ORM objects)
 * 10.
 */
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'sample3.php',
        'method' => 'post'
    ]
);

$form->addText([
    'name' => 'NAME',
    'label' => 'Your Name',
    'mandatory' => true,
]);

$form->addText([
    'name'  =>  'EMAIL',
    'label' =>  'Your Email',
    'mandatory' => true,
    'datatype'  => 'email'
]);

$form->addTextarea([
    'name'=>'MESSAGE',
    'label'=>'Message',
    'rows'=>'4',
    'cols'=>'50',
    'mandatory' => true
]);

$form->addCaptcha([
    'name'=>'CAPTCHA',
    'secret' => 'blahblah'
]);

$form->addSubmit([
    'name'=>'submit',
    'value'=>'Submit'
]);

$form->finalize();

//THE ONLY LINE OF CODE YOU NEED TO ADD TO DISPLY USING BOOTSTRAP
$form->renderer = new X2Form\Renderers\Bootstrap\Renderer();

//handle the form submission
if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" ){

    $form->setValues( $_POST );
    $log = $form->processSubmission( $_POST );
    if( !logg_ok( $log ) ){
        $message = '<p class="text-danger">'. logg_msg( $log ).'</p>';
        $form->rollBackFileUploads();
    }else{
        $message = '<p class="text-success">'. logg_msg( $log ).'</p>';

    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <title>Rendering form using Bootstrap</title>

    <!-- HERE WE INCLUDE BOOTSTRAP CSS -->
    <link rel="stylesheet" href="../../vendor/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../vendor/bootstrap/dist/css/bootstrap-theme.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <h1>Example 3</h1>
            <h3>Rendering the form using bootstrap</h3>
            <p>Here we are rendering the form created in example 2 using Bootstrap</p>
            <p>Besides including the css and making some bootstrap containers, all you need to do is </p>
            <pre>$form->renderer = new X2Form\Renderers\Bootstrap\Renderer();</pre>
            <p>This allows you switch between tables, bootstrap etc without changing your php code.</p>
        </div>

        <div class="col-md-6 col-sm-12">
            <h2>Contact Us</h2>
        <?php
        if( isset( $message) && $message ){
            echo $message;
        }
        echo $form->render();
        ?>
        </div>
    </div>
<?php
//lets print the submitted data if the validation was successful
if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" && logg_ok( $log ) ){
    ?>
    <div class="row">
        <h2>Submited values</h2>
        <?php var_dump( $form->getValues() ); ?>
    </div>
<?php } ?>
</div>
</body>
</html>
