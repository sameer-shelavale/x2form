<?php
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example3.php',
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
                'type'=>'collection',
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
            ],
            [
                'type'=>'captcha',
                'name'=>'CAPTCHA',
                'secret' => 'blahblah'
            ],
            //Now we GROUP up the submit and reset buttons.
            [
                'type'=>'group',
                'name'=>'buttons',
                'direction' => 'inline', //places elements horizontally using white space(&nbsp;) as separator
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
            ]

        ]
    ]
);

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
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <title>Example 3 - Collection</title>
    <link href="../css/style.css" type="text/css" rel="stylesheet">
    <script type="text/javascript" src="../js/jquery-ui-1.9.2.custom/js/jquery-1.8.3.js" ></script>
</head>
<body>

<div id="outputContainer">
    <div class="container">
        <h2>Sample Contact Us Form</h2>
        <?php
        //lets print the submitted data if the validation was successful
        if( isset( $message) && $message ){
            echo $message;
        }
        echo $form->render();
        ?>
    </div>

    <?php
    if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" && logg_ok( $log ) ){
        ?>
        <div class="container">
            <h2>Submited values</h2>
            <?php
            var_dump( $form->getValues() );
            ?>
        </div>
    <?php
    }
    ?>
</div>
<div id="codeContainer">
    <div class="container">
        <h1>Example 3 - Collection</h1>
        <h3>Multiple occurances of a set of fields.</h3>
        <p>
            Collection is essentially forms within a form, it allows you to create multiple sets/rows of fields of predefined type dynamically.
        </p>
        <code><pre>
$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example3.php',
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
            <b>[
                'type'=>'collection',
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
            ],</b>
            [
                'type'=>'captcha',
                'name'=>'CAPTCHA',
                'secret' => 'blahblah'
            ],
            //Now we GROUP up the submit and reset buttons.
            [
                'type'=>'group',
                'name'=>'buttons',
                'direction' => 'inline', //places elements horizontally using white space(&nbsp;) as separator
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
            ]

        ]
    ]
);
            </pre></code>
        <p>
            Note:<br/>
            Collection needs jQuery to render the rows dynamically, make sure you include jQuery in your page.<br/>
            For now collection only allows horizontal placement, but vertical placement with row summary is also planned for upcoming versions.
            <br/>
            Avoid creating nested collections.
        </p>
    </div>
</div>

</body>
</html>
