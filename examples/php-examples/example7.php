<?php
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example7.php',
        'method' => 'post',
        'template'=> 'template-for-example7.php',
        'renderer'=> new X2Form\Renderers\Bootstrap\Renderer()
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
    'options'  => [ 'array'=> ['male', 'female'] ],
]);

$form->addCheckbox([
    'name'  =>  'LANGUAGES',
    'label' =>  'Languages known',
    'mandatory' => true,
    'options'  => [ 'array'=> ['English', 'Hindi', 'French', 'German'] ],
    'direction'=>'vertical'
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
    <title>Example 7 - Customizing layout of the form using templates</title>

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
            <h1>Example 7</h1>
            <h3>Customizing the layout of form-elements using templates</h3>
            <p>Here we are rearranging the layout of  the form created in example 6</p>
            <p>The X2Form as is, can display form elements in vertical or horizontal arrangement,
               this caters well for mobile divices, but many times for larger screens we need more complex
                layouts where the elements are arranged in multiple rows or columns.
                To achieve these complex layouts we can create templates, which depict exactly which form field is to be placed where.

            </p>
            <p>
                In a template we can place three types of placeholders related to form fields,
                <br/>1. [<b>ELEMENT_NAME</b>] The element itself, it is replaced by the html of the form-field
                <br/>2. [ELEMENT_NAME<b>_label</b>] The label, it is replaced by the label for the element
                <br/>3. [ELEMENT_NAME<b>_description</b>] The description, it is replaced by the description

            </p>
            <p>Lets look at the template used for this example</p>
            <code><pre><xmp>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body>
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="form-group">
                [NAME_label]
                [NAME]
            </div>
            <div class="form-group">
                [EMAIL_label]
                [EMAIL]
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="form-group">
                [EXPERIENCE_label]
                [EXPERIENCE]
            </div>
            <div class="form-group">
                [GENDER_label]
                [GENDER]
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group">
                [work_experience_label]
                [work_experience]
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="form-group">
                [MESSAGE_label]
                [MESSAGE]
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="form-group">
                [CAPTCHA_label]
                [CAPTCHA]
                [CAPTCHA_description]
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12">
            [buttons]
        </div>
    </div>
</body>
</html>
            </xmp></pre></code>
            <p>Note1: Only the code <u>WITHIN</u> the <code>&lt;body&gt;</code> tag is used as a template, the whole header is discarded.
                You may wonder why so? It is done so that you can design the template using visual IDE/editors to create those layouts. </p>
            <p>Note2:  You may also use .php extension for templates if you need some minor logical/conditional operations to be carry out there.</p>
            <p>Note3: The wrapper <code>&lt;div class="form-group"&gt;</code> needs to be added the template, but when NOT using templates, its added automatically.</p>
            <p>Note4: We don't use the bootstrap helper classes(has-error) for indicating error as they need to be applied to the parent of the form field. we use a custom class <i>errorfield</i> which is applied on the fields directly. </p>
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
