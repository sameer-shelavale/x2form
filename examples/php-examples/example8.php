<?php
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example8.php',
        'method' => 'post',
        'template'=> 'template-for-example8.php',
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
    'cols'=>'25',
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
    <title>Example 8 - Customizing layout of the form using templates</title>
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
        <h1>Example 8 customizing layout using templates</h1>
        <p>
            Example 5 used tables to render the form,  here we customize layout of the form in example 5 using templates.
        </p>
        <code><pre><xmp>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
</head>
<body>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr class="odd">
        <td valign="top" width="50%">
            [NAME_label] <br/>
            [NAME] &nbsp; <i>[NAME_description]</i><br/><br/>
            [GENDER_label]<br/>
            [GENDER] &nbsp; <i>[GENDER_description]</i>
        </td>
        <td valign="top">
            [LANGUAGES_label]<br/>
            [LANGUAGES] &nbsp; <i>[LANGUAGES_description]</i>
        </td>
    </tr>
    <tr class="even">
        <td valign="top">
            [EMAIL_label]<br/>
            [EMAIL] &nbsp; <i>[EMAIL_description]</i>
        </td>
        <td valign="top">
            [EXPERIENCE_label]<br/>
            [EXPERIENCE] &nbsp; <i>[EXPERIENCE_description]</i>
        </td>
    </tr>
    <tr class="even">
        <td valign="top" colspan="2">
            [work_experience_label]<br/>
            [work_experience] &nbsp; <i>[work_experience_description]</i>
        </td>
    </tr>
    <tr class="odd">
        <td valign="top">
            [MESSAGE_label]<br/>
            [MESSAGE] &nbsp; <i>[MESSAGE_description]</i>
        </td>
        <td valign="top">
            [CAPTCHA_label]<br/>
            [CAPTCHA] &nbsp; <i>[CAPTCHA_description]</i>
        </td>
    </tr>
    <tr class="odd">
        <td>[buttons]</td>
    </tr>
</table>
</body>
</html>

        </xmp></pre></code>
    </div>
</div>

</body>
</html>
