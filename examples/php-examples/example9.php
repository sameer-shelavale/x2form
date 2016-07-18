<?php
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example9.php',
        'method' => 'post'
    ]
);

$form->addStep([
    'index' => 1,
    'name'=>'step1', //name should never contain white spaces or special characters
    'label' => 'Step 1:',
    'description'=>'User Data',
    'elements'=>[
        [
            'type' => 'text',
            'name' => 'NAME',
            'label' => 'Your Name',
            'mandatory' => true,
            'value' => 'Rudra' // we can populate values in the
        ],
        [
            'type' => 'text',
            'name'  =>  'EMAIL',
            'label' =>  'Your Email',
            'mandatory' => true,
            'datatype'  => 'email',
            'ifempty' => 'Please provide email address.',
            'ifinvalid' => 'The email address you provided is invalid.',
        ]
    ]
]);

$form->addStep([
    'index' => 2,
    'name'=>'step2',
    'label' => 'Step 2:',
    'description'=>'Personal Information',
    'elements'=>[
        [
            'type' => 'radio',
            'name'  =>  'GENDER',
            'label' =>  'Gender',
            'mandatory' => true,
            'options'  => [ 'array'=> ['male', 'female'] ],
        ],
        [
            'type' => 'checkbox',
            'name'  =>  'LANGUAGES',
            'label' =>  'Languages known',
            'mandatory' => true,
            'options'  => [ 'array'=> ['English', 'Hindi', 'French', 'German'] ],
            'direction'=>'vertical'
        ]
    ]
]);

$form->addStep([
    'index' => 3,
    'name'=>'step3',
    'label' => 'Step 3:',
    'description'=>'Personal Information',
    'elements'=>[
        [
            'type' => 'dropdown',
            'name'  =>  'EXPERIENCE',
            'label' =>  'Experience',
            'mandatory' => true,
            'options'  => [ 'array'=> ['fresher', '6 months+', '1 year', '2 years', '3 years', '4 years', '5 years', '5-10 years', '10-15 years', '15+ years'] ],
            'value' => '1 year'
        ],
        [
            'type' => 'collection',
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
        ]
    ]
]);

$form->addStep([
    'index' => 4,
    'name'=>'step4',
    'label' => 'Step 4:',
    'description'=>'Are you Human?',
    'elements'=>[
        [
            'type' => 'captcha',
            'name'=>'CAPTCHA',
            'secret' => 'blahblah'
        ]
    ]
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
    <title>Example 9 - Implementing Steps</title>
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
        <h1>Example 9 customizing layout using templates</h1>
        <p>
            Job application form divided in steps
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
