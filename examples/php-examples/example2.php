<?php
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example2.php',
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
    <title>Example 2 - Group</title>
    <link href="../css/style.css" type="text/css" rel="stylesheet">
</head>
<body>

    <div id="outputContainer">
        <div class="container">
            <h2>Contact Us</h2>
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
            <h1>Example 2 - Group</h1>
            <h3>Horizontal or inline alignment of multiple elements in a row using <b>"group"</b>.</h3>
            <p>
                Grouping up elements is quiet easy. Just pass them in the <i>elements</i> of the group tag.
                Remember to give a <i>name</i> to your <i>group</i>.
            </p>
            <code><pre>
$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'example2.php',
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
            //Now we GROUP up the submit and reset buttons.
            <b>[
                'type'=>'group',
                'name'=>'buttons',
                'direction' => 'inline',
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
            ]</b>
        ]
    ]
);
            </pre></code>
            <p>
                Note: The Group is added basically to avoid the need of creating custom template,
                for a small thing like displaying the buttons horizontally, but it can also handle other
                elements as long as the width of the container or your visual design permits.
                <br/>
                It is recommended to avoid creating nested groups,
                instead use <i>template</i> to achieve the desired visual appearance.
            </p>
        </div>
    </div>

</body>
</html>
