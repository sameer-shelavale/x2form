<?php
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example5.php',
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

//handle the form submission
if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" ){
    $log = $form->processSubmission( $_POST );
    if( !logg_ok( $log ) ){
        $message = '<div class="error">'. logg_msg( $log ).'</div>';
        $form->rollBackFileUploads();
    }else{
        $message = '<div class="success">'. logg_msg( $log ).'</div>';

    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <title>Example 5 - Populating/setting field values</title>
    <link href="../css/style.css" type="text/css" rel="stylesheet">
    <script type="text/javascript" src="../js/jquery-ui-1.9.2.custom/js/jquery-1.8.3.js" ></script>
</head>
<body>

<div id="outputContainer">
    <div class="container">
        <h2>Sample Job Application Form</h2>
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
    <div class="container">
        <h2>Submited values</h2>
        <?php var_dump( $form->getValues() ); ?>
    </div>
<?php } ?>
</div>

<div id="codeContainer">
    <div class="container">
        <h1>Example 5</h1>
        <h3>Populating/setting field values</h3>
        <p>
            We will use a Job application form to demonstrate setting values on different types of fields.
        </p>
        <code><pre>
$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example5.php',
        'method' => 'post'
    ]
);

$form->addText([
    'name' => 'NAME',
    'label' => 'Your Name',
    'mandatory' => true,
    <b>'value' => 'Rudra'</b> // we can populate values in the
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
    <b>'value'   => '1 year'</b>
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
<b>$values = array(
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
</b>
//Alternatively we can also populate individual fields directly, except for group and collection types
<b>$form->elements['EMAIL']->value = 'rudra@techrevol.com';</b>	//array syntax
<b>$form->elements->MESSAGE->value = "I am Rudra!";</b> //this syntax works as well


$form->finalize();

        </pre></code>

    </div>
</div>

</body>
</html>
