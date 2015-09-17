<?php
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'sample1.php',
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
	<title>Constructing form using only php(default constructor)</title>
	<link href="../css/style.css" type="text/css" rel="stylesheet">
</head>
<body>
   <div id="outputContainer">
        <div class="container">
            <h2>Contact Us</h2>
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
           <h1>Example 1</h1>
           <h3>Constructing a simple contact us form using only PHP calls(using constructor params to add elements)</h3>
           <p>
               Our constructor looks like
           </p>
           <code>
            <pre>
$form = new \X2Form\Form(
    'ContactForm',
    [
        'action' => 'sample1.php',
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
            <b>[
                'type'=>'submit',
                'name'=>'submit',
                'value'=>'Submit'
                ]</b>,
            [
                'type'=>'reset',
                'name'=>'reset',
                'value'=>'Reset'
            ]
        ]
    ]
);
            </pre></code>

           <p>
               Once you are done setting up the form its recommended to call the finalize() function.
               This function computes the data like options for  select or radio or checkboxes,
               which may involve running queries or filtering through arrays.
               This function also sets up service provider object for Captcha elements
               and passes the captcha options as parameters to it.
               This function also ensures correct element parents are setup.
           </p>
           <pre><code>$form->finalize();</code></pre>
           <p>Once finalized, all you need to render the form is call the render() function.</p>
           <pre><code>$form->render();</code></pre>
           <p>
               Oh yes, the Submit and Reset buttons are displayed vertically.
               By, default the elements are all displayed vertically.
               In order to display only those two buttons horizontally we can use <i>group</i>.
               In next example we will see how to use <i>group</i>.
           </p>
       </div>
   </div>

</body>
</html>
