<?php
use \X2form\Form;
require_once('../../src/autoload.php');
require_once('../../vendor/autoload.php');

$link = mysql_connect('localhost', 'root', '');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
$db_selected = mysql_select_db('test', $link);
if (!$db_selected) {
    die ('Can\'t select database : ' . mysql_error());
}

//create the X2Form object from XML file
$formObj = new Form(
    'QuidichForm',
    [
        'from'=> 'forms/quidich_form_grouped.xml',
        'dbHandle' => $link,
        'dbType'=>'php'
    ]
);
$formObj->finalize();
if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" ){
    $log = $formObj->processSubmission( $_POST );
    if( !logg_ok( $log ) ){
        $message = '<div class="error">'. logg_msg( $log ).'</div>';
        $form->rollBackFileUploads();
    }else{
        $message = '<div class="success">'. logg_msg( $log ).'</div>';
    }
}elseif( isset( $_REQUEST['captcha'] ) && $_REQUEST['captcha'] == 'refresh' ){
    echo $formObj->renderer->elementRenderer->refreshCaptcha( $formObj->elements['captcha'] );
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <title>A simple HTML form using X2Form</title>
    <link href="../css/style.css" type="text/css" rel="stylesheet">
    <script type="text/javascript" src="../js/jquery-ui-1.9.2.custom/js/jquery-1.8.3.js" ></script>
</head>
<body>
<div id="outputContainer">
    <div class="container">
        <h2>Sample Quidich Tournament Application Form </h2>
        <?php
        if( isset( $message) && $message ){
            echo $message;
        }
        echo $formObj->render(); ?>
    </div>
    <?php
    //lets print the submitted data if the validation was successful
    if( isset( $_POST['submit'] ) && $_POST['submit'] == "Submit" && logg_ok( $log ) ){
        ?>
        <div class="container">
            <h2>Submited values</h2>
            <?php var_dump( $formObj->getValues() ); ?>
        </div>
    <?php } ?>
</div>
<div id="codeContainer">
    <div class="container">
        <h1>Example 2: Collection and Group</h1>
        <h3>This example demonstrates the use of Collection and Group tag for legacy XML type form definitions</h3>
        <p>
            The collection syntax looks like
        </p>
        <code>
            <xmp>
        <collection name="TEAM_MEMBERS" label="Team Members" groupcontainer="div">
            <schema>
                <elements>
                    <element type="text" name="FIRST_NAME" label="First Name" ></element>
                    <element type="text" name="LAST_NAME" label="Last Name" ></element>
                </elements>
            </schema>
        </collection>
            </xmp>
        </code>
        <p>
            Note that the legacy tags in collection like headertemplate, itemtemplate, listfields are ignored for now, and will be revised later.
        </p>
        <p>The syntax for group in xml is</p>
        <code>
            <xmp>
        <group name="buttons" direction="inline">
            <element type="submit" name="submit" value="Submit" class="siteButtons saveDiamondButton"></element>
            <element type="submit" name="dump" value="Submit &amp; dump data" class="siteButtons saveDiamondButton"></element>
            <element type="button" name="eventbutton" value="Click event" class="siteButtons saveDiamondButton">
                <events>
                    <event type="onclick"><![CDATA[ alert("This click event originates from XML definition of this form!")  ]]></event>
                </events>
            </element>
        </group>
            </xmp>
        </code>

        <p>Rest remains same.</p>
    </div>
</div>

</body>
</html>