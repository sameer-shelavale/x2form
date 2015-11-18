<?php
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example4.php',
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
    <title>Example 4 - Constructing a form in example 3 using only PHP calls(using add* methods)</title>
    <link href="../css/style.css" type="text/css" rel="stylesheet">
    <script type="text/javascript" src="../js/jquery-ui-1.9.2.custom/js/jquery-1.8.3.js" ></script>
</head>
<body>

<div id="outputContainer">
    <div class="container">
        <h2>Sample Contact Us Form</h2>
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
        <h1>Example 4</h1>
        <h3>Constructing a form in example 3 using only PHP calls(using add* methods)</h3>
        <p>
            You can also create the form in example 3 without passing the elements in constructor.
            Instead you can use the add* functions.
        </p>
        <code><pre>
$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example4.php',
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

        </pre></code>
        <p>
            Note that, you need not specify the <i>type</i> for element you are adding with add* function.
        </p>
        <p>
            The add* functions are useful when you already have a form created(from ORM objects or database tables) and you just want to add some extra fields.
        </p>

    </div>
</div>

</body>
</html>
