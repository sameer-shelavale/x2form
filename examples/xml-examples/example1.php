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
        'from'=> 'forms/quidich_form.xml',
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
            <h1>Example 1 Creating form from XML</h1>
            <h3>This example demonstrates the support for legacy XML type form definitions</h3>
            <p>
                Our xml definition of the form looks like
            </p>
            <code>
                <xmp>
<?xml version="1.0" encoding="UTF-8"?>
<form action="example1.php" method="POST" enctype="multipart/form-data">
    <elements>
        <!-- example of labels -->
        <element type="label" name="LABEL1" value="Annual Quidich Tournament Application Form" style="font-weight:bold" ></element>

        <!-- example of text input -->
        <element type="text" name="FIRST_NAME" label="First Name" ></element>

        <!-- example of text input with tooltip( specified by title attribute to keep similarity with html syntax) -->
        <element type="text" name="LAST_NAME" label="Last Name" title="Enter your Last Name here."></element>

        <!-- example of mandatory text input with 'email' datatype -->
        <element type="text" name="QMAIL" label="Email" datatype="email" mandatory="true" ></element>

        <!-- example of dropdown with description. Description is displayed after the element by default, but with templates it can be customized -->
        <element type="dropdown" name="PROFESSION" label="Profession" mandatory="true" description="You need not apply if your profession is not listed here." >
            <options>
                <option value="" label="Select Profession" />
                <option value="seeker" label="Seeker" />
                <option value="keeper" label="Keeper" />
                <option value="blindsidebeater" label="Blind Side Beater" />
                <option value="opensidebeater" label="Open Side Beater" />
                <option value="centerchaser" label="Center Chaser" />
                <option value="outsidechaser" label="Outside Chaser" />
            </options>
        </element>

        <!-- another example of dropdown with prompt, and options populated from result of a mysql query,
             Note the use of valueField and labelField attributes to specify fields to be used for value and label
         -->
        <element type="dropdown" name="HOUSE" label="Which house you are from?" prompt="Select your house" >
            <options>
                <query valueField="HOUSE_ID" labelField="NAME"><![CDATA[
                    SELECT * FROM houses;
                    ]]></query>
            </options>
        </element>

        <!-- checkboxes arranged vertically, using 'direction' attribute -->
        <element type="checkbox" name="BROOMSTICKS" label="Select the broomsticks you have" direction="vertical" >
            <options>
                <option value="cleansweep" label="Cleansweep" />
                <option value="nimbus" label="Nimbus" />
                <option value="nimbus2000" label="Nimbus 2000" />
                <option value="nimbus2001" label="Nimbus 20001" />
                <option value="firebolt" label="Firebolt" />
            </options>
        </element>

        <!-- example of a single checkbox -->
        <element type="checkbox" name="RECEIVE_OFFERS" label="Receive special offers?">
            <options>
                <option value="yes" label="Yes"/>
            </options>
        </element>

        <!-- example of radio input arranged vertically -->
        <element type="radio" name="MATCHES_PLAYED" label="How many matches have you played?" direction="vertical" >
            <options>
                <option value="0" label="0"/>
                <option value="1-5" label="1 to 5"/>
                <option value="6-10" label="6 to 10"/>
                <option value="11-25" label="11 to 25"/>
                <option value="26-100" label="26 to 100"/>
                <option value="100+" label="more than 100"/>
            </options>
        </element>

        <!-- example of radio input arranged vertically -->
        <element type="radio" name="IS_SEPARABLE" label="Is Separable?" >
            <options>
                <option value="True" label="Yes"/>
                <option value="False" label="No"/>
            </options>
        </element>

        <!-- example of textarea -->
        <element type="textarea" name="INTRO" label="Write something about yourself" rows="5" cols="50">test string</element>

        <!-- example of file input with Maximum file size allowed = 2mb, only jpg,png,jpeg extensions allowed
            It has also specified the directory where to put the file on successful submission
        -->
        <element type="file" name="PHOTO" label="Upload your photo" maxsizemb="2" allowextensions="jpg,png,jpeg" iffileexists="renamenew" uploaddirectory="uploaded_files/"></element>

        <element type="file" name="PORTFOLIO" description="Only in pdf format" label="Upload your portfolio" mandatory="true" maxsizemb="2" allowextensions="pdf" ></element>

        <element type="captcha" name="captcha" secret="secret-key1" refreshUrl="sample1.php?captcha=refresh" helpUrl="" >
            <options>
                <option type="gif" maxCodeLength="8" width="180" height="60" totalFrames="50" delay="20" />
                <option type="math" description="Answer following question if you are human" level="4" />
            </options>
        </element>

        <!-- example of hidden inputs -->
        <element type="hidden" name="USER_ID" value="0" ></element>

        <!-- example of hidden submit buttons -->
        <element type="submit" name="submit" value="Submit" class="siteButtons saveDiamondButton"></element>
        <element type="submit" name="dump" value="Submit &amp; dump data" class="siteButtons saveDiamondButton"></element>

        <!-- example of normal button with onclick event -->
        <element type="button" name="eventbutton" value="Click event" class="siteButtons saveDiamondButton">
            <events>
                <event type="onclick"><![CDATA[ alert("This click event originates from XML definition of this form!")  ]]></event>
            </events>
        </element>
    </elements>
</form>
                </xmp>
            </code>
            <p>And we load the xml just like:</p>
            <code><pre>
$formObj = new Form(
    'QuidichForm',
    ['from'=> 'forms/quidich_form.xml']
);
            </pre></code>
        </div>
    </div>

</body>
</html>