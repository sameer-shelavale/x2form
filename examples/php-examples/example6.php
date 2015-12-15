<?php
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example6.php',
        'method' => 'post'
    ]
);

$form->addText([
    'name' => 'NAME',
    'label' => 'Your Name',
    'mandatory' => true,
    'value' => 'Rudra' // we can populate values in the
]);

$form->addText([
    'name'  =>  'EMAIL',
    'label' =>  'Your Email',
    'mandatory' => true,
    'datatype'  => 'email'
]);

$form->addRadio([
    'name'  =>  'GENDER',
    'label' =>  'Gender',
    'mandatory' => true,
    'options'  => [ 'array'=> ['male', 'female'] ]
]);

$form->addDropdown([
    'name'  =>  'EXPERIENCE',
    'label' =>  'Experience',
    'mandatory' => true,
    'options'  => [ 'array'=> ['fresher', '6 months+', '1 year', '2 years', '3 years', '4 years', '5 years', '5-10 years', '10-15 years', '15+ years'] ],
    'value' => '1 year'
]);

$form->addTextarea([
    'name'=>'MESSAGE',
    'label'=>'Message',
    'rows'=>'4',
    'cols'=>'50',
    'mandatory' => true
]);
$form->addCollection([
    'name' => 'work_experience',
    'label' => 'Work Experience',
    'from' => [
        [
            'type' => 'text',
            'name' => 'company',
            'label' => 'Company',
            'mandatory' => true,
        ],
        [
            'type' => 'text',
            'name' => 'role',
            'label' => 'Role',
            'mandatory' => true,
        ],
    ]
]);
$form->addCaptcha([
    'name'=>'CAPTCHA',
    'secret' => 'blahblah'
]);

$form->addGroup([
    'name'=>'buttons',
    'direction'=>'inline',
    'elements'=>[
        [
            'type'=>'submit',
            'name'=>'submit',
            'value'=>'Submit'
        ],
        [
            'type'=>'reset',
            'name'=>'reset',
            'value'=>'Reset'
        ]
    ]
]);

//we can also populate fields from php using setValues() method of the X2Form class
$values = array(
    'work_experience'=>array(
        array(
            'company' => 'Possible Solutions',
            'role'=> 'Junior Developer'
        ),
        array(
            'company' => 'Tech Revolution',
            'role'=> 'Senior Developer'
        ),
    ),
    'GENDER'=>'male'
);

$form->setValues( $values );


//Alternatively we can also populate individual fields directly

$form->elements['EMAIL']->value = 'rudra@techrevol.com';	//array syntax
$form->elements->MESSAGE->value = "I am Rudra!"; //this syntax works as well

$form->finalize();

//The line below is only code required to display in bootstrap, besides including the css and js
$form->renderer = new X2Form\Renderers\Bootstrap\Renderer();

//handle the form submission
if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" ){
    $log = $form->processSubmission( $_POST );
    if( !logg_ok( $log ) ){
        $message = '<p class="text-danger">'. logg_msg( $log ).'</p>';
        $form->rollBackFileUploads();
    }else{
        $message = '<p class="text-success">'. logg_msg( $log ).'</p>';

    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <title>Example 6 - Populating/setting field values</title>

    <!-- HERE WE INCLUDE BOOTSTRAP CSS -->
    <link rel="stylesheet" href="http://localhost/Bootstrap-3-Offline-Docs/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="http://localhost/Bootstrap-3-Offline-Docs/dist/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="../css/x2form-bootstrap.css">

    <script type="text/javascript" src="../js/jquery-ui-1.9.2.custom/js/jquery-1.8.3.js" ></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <h1>Example 6</h1>
            <h3>Rendering the form using Bootstrap</h3>
            <p>Here we are rendering the form created in example 4 using Bootstrap</p>
            <p>Besides including the css, jQuery(for rendering collection) and making some bootstrap containers, all you need to do is </p>
            <pre>$form->renderer = new X2Form\Renderers\Bootstrap\Renderer();</pre>
            <p>This allows you switch between tables, bootstrap etc without changing your php code.</p>

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


        <h2>Sample Job Application Form</h2>
    <?php
    if( isset( $message) && $message ){
        echo $message;
    }
    echo $form->render(); ?>

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
