x2form
======

X2Form is a form generator/builder developed for easy creation & maintenance of web forms.

The X2Form architecture separates the definition of the form elements, rendering of the elements, positioning of the elements(layout/templates) and processing/validation of the form.
This makes it very easy to update and maintain the forms in long term adding and removing more fields is extremely easy.
 
##Main features:
1.  It can create web forms from
  *  Laravel/Eloquent models or objects.
  *  mysql tables.
  *  pure PHP calls by creating a X2Form\Form object and adding elements to it using the add* methods in Form class.
  *  XML definition of the form.
2.  It can read values of dropdowns, checkboxes and radio from PHP functions, PHP Closures, PHP Global variables MYSQL queries.
3.  Supports HTML/PHP template for customizing the form layout
4.  Can handle file uploads, it can also rollback the file system changes if something goes wrong.
5.  It can do validation of the form values depending on the 'datatype' or 'datapattern'(using regular expressions)
6.  Values to be  pre-populated in the form can be passed in as array
7.  Multi-Language support, you can define labels, tooltips, description as well as error messages in multiple languages.
    Thus making your form to render properly in multiple languages
8.  Easily extensible. Adding extra types of form elements or add more types of loaders, renderers and templates is easy.
9.  Clean name-spaced code
10. Easy installation using Composer.


## Installation & Configuration:

#### Using Composer Command
You can install X2Form on command line in your project using Composer by running
```
composer require sameer-shelavale/x2form
```

#### composer.json
Alternatively, you can add it directly in your composer.json file in require block

```
{
    "require": {
        "sameer-shelavale/x2form": "2.1.*"
    }
}
```

and then run `composer update`

#### PHP Include
You can also download the zip/rar archive and extract it and copy the folder to appropriate location in your project folder.
And then include the autoload.php in your code

You also need to download [MultiCaptcha](https://github.com/sameer-shelavale/multi-captcha) and include it
```
include_once( 'PATH-TO-X2FORM-FOLDER/src/autoload.php' );
include_once( 'PATH-TO-MULTICAPTCHA-FOLDER/src/Captcha.php' );
```

## Supported form controls/elements
X2Form supports following HTML controls:
*  text
*  textarea
*  checkbox
*  radio
*  dropdown
*  file
*  label
*  button ( i.e. `<input type="button" >` )
*  submit (i.e. `<input type="submit" >`)
*  reset (i.e. `<input type="reset" >` )
*  hidden (i.e. `<input type="hidden" >` )
*  image (i.e. `<input type="image" >` )
*  captcha

## Usage
You can initialize an empty form just like

```php
$form = new \X2Form\Form( 'MyFormName' );
```
MyFormName is the name of the form

```php
$form = new \X2Form\Form(
    'MyFormName',
    [
        'action' => 'index.php',
        'method' => 'post'
    ]
);
```

#### Parameters
| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *from*     | null  | Optional | An Eloquent ORM object(in Laravel framework) or  xml file or xml string
| *template* | false | Optional | full path of the template file |
| *language* | false | Optional | code of the language to be used for displaying the texts |
| *dbType*   | php   | Optional | supported values are php(Queries will be executed using the mysql_query() and related functions ), pdo (PHP-PDO library will be used to execute queries), joomla(Joomla db object and functions will be used.) |
| *dbHandle* | false | Optional | the database handle, required only if you need to run queries using php or pdo libs |
| *renderer* | new Renderers\Table\Renderer() | Optional | defaults is object of 'Renderers\Table\Renderer' class. Supported renderers are Renderers\Table\Renderer and Renderers\Bootstrap\Renderer |
| *loader*   | new Loaders\Auto()  | Optional | if you need to load form elements from something not supported by this library, you can write your own loader class and pass its object to this parameter. Remeber custom loaders must implement interface *X2Form\Interfaces\Loader*

## Form controls/elements
You can use constructor param named *elements* or after you initialize the form object you can use add* functions or the load method to load the elements from ORM objects or XML files or XML string or SimpleXML Object.
First we will see the available element types and how to add them in the form.


### text
This element creates a *textbox* or `<input type="text">` control in the form

**Basic parameters:**
| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *name*     | Required | name of the textbox
| *label* | Optional | Label to be displayed for the textbox |
| *description* | Optional | Description, generally displayed after the textbox(unless implemented otherwise using templates or custom renderer ) |
| *id* | Optional | unique id for the textbox, its actually the id attribute |
| *value* | Optional | String, to be displayed in textbox.
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event |

**Advanced configuration parameters:**
Configuration attributes are mainly used for form validation or formatting the display

| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *mandatory* | (boolean) false | Optional | Defines whether the element(control) in the form is mandatory or not, by default the fields are NOT mandatory. If the value is  if the value is *true*, *yes*, or *1* then it means the field is mandatory else the field is not mandatory. |
| *dataType* | - | Optional | Specifies the expected data-type of the submitted value of the field. It is used during validation. supported values are: Supported values: *integer*, *number* (decimal numbers), *email* ( see emailCheckDNS also ), *date*, *time*, *datetime*, *color* ( html color format e.g. #123FFF ), *url*, *ip* (accepts both ipv4 and ipv6 addresses), *ipv4* (only IP v4 address), *ipv6* (only IP v6 address) |
| *dataPattern* | - | Optional | Regular Expression to match with the submitted value. It can be used in cases where the dataType is not sufficient itself or if the type/format of expected value is different than the supported dataTypes. If not specified or if its empty or invalid, it is ignored. |
| *emailCheckDNS* | (boolean) false | Optional | This is effective only when passed with *dataType=email* otherwise it is ignored and has no effect. If its true, it will check if the domain of the email exists or not by checking the DNS. So this attribute should NOT be set to true if your web-server is running in offline mode or is not connected to internet. Possible values: Boolean ( *true* / *false* , *yes* / *no*, *on* / *off*, *1* / *0* ). if value is *true*, *on*, *yes*, *1* it is true, else its false |
| *min* | - | Optional | This attribute is used with *dataType=integer* or *dataType=number*. It is used to specify minimum limit/value of the integer/number. If not specified it is ignored.|
| *max* | - | Optional | This attribute is used with *dataType=integer* or *dataType=number*. It is used to specify maximum limit of the integer/number. If not specified it is ignored.|.

***IMP NOTE: besides all of the above basic and advanced params, all other normal HTML attributes for the `<input type="text">` tag like *style*, *class* etc. can also be passed.

```php
$form->addText([
    'name'=>'FIRST_NAME',
    'label'=>'First Name'
]);
```

### textarea
This element creates a `<textarea>` control in the form

**Basic parameters:**
| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *name*     | Required | name of the textarea
| *label* | Optional | Label to be displayed for the textarea |
| *description* | Optional | Description, generally displayed after/below the textarea(unless implemented otherwise using templates or custom renderer ) |
| *id* | Optional | unique id for the textarea, its actually the id attribute |
| *value* | Optional | text to be displayed in textarea.
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event |

**Advanced configuration parameters:**
Configuration attributes are mainly used for form validation or formatting the display

| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *mandatory* | (boolean) false | Optional | Defines whether the element(control) in the form is mandatory or not, by default the fields are NOT mandatory. If the value is  if the value is *true*, *yes*, or *1* then it means the field is mandatory else the field is not mandatory. |

***IMP NOTE: besides all of the above basic and advanced params, all other normal HTML attributes for the `<textarea>` tag like *style*, *class* etc. can also be passed.

```php
$form->addTextarea([
    'name'=>'INTRO',
    'label'=>'Write something about yourself',
    'rows'=>'5',
    'cols'=>'50',
    'value'=>'I am Harry... Harry Potter.'
]);
```

### dropdown
This element creates a `<select>` control in the form

**Basic parameters:**
| Param | Required | Description |
| ----- |--------- | ----------- |
| *name*     | Required | name of the dropdown
| *label* | Optional | Label to be displayed for the dropdown |
| *description* | Optional | Description, generally displayed after the dropdown(unless implemented otherwise using templates or custom renderer ) |
| *id* | Optional | unique id for the dropdown, its actually the id attribute |
| *value* | Optional | String, to be displayed in dropdown. |
| *prompt* | Optional | String. Some times we may need to display a default text in the dropdown. For example. we may need to display "Select your country" in the dropdown when no country is selected, in such cases its accomplished using the prompt. |
| *options* | Required | This param specifies the options displayed in the dropdown. There are multiple ways to define options like php array, sql query, php global variable or anonymous functions(lambda functions in php). We will learn these in details below. This parameter is required as without it the dropdown wont have any options to display. |
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event |

**Advanced configuration parameters:**
Configuration attributes are mainly used for form validation or formatting the display

| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *mandatory* | (boolean) false | Optional | Defines whether the element(control) in the form is mandatory or not, by default the fields are NOT mandatory. If the value is  if the value is *true*, *yes*, or *1* then it means the field is mandatory else the field is not mandatory. |
| *dataType* | - | Optional | Specifies the expected data-type of the submitted value of the field. It is used during validation. supported values are: Supported values: *integer*, *number* (decimal numbers), *email* ( see emailCheckDNS also ), *date*, *time*, *datetime*, *color* ( html color format e.g. #123FFF ), *url*, *ip* (accepts both ipv4 and ipv6 addresses), *ipv4* (only IP v4 address), *ipv6* (only IP v6 address) |
| *dataPattern* | - | Optional | Regular Expression to match with the submitted value. It can be used in cases where the dataType is not sufficient itself or if the type/format of expected value is different than the supported dataTypes. If not specified or if its empty or invalid, it is ignored. |
| *emailCheckDNS* | (boolean) false | Optional | This is effective only when passed with *dataType=email* otherwise it is ignored and has no effect. If its true, it will check if the domain of the email exists or not by checking the DNS. So this attribute should NOT be set to true if your web-server is running in offline mode or is not connected to internet. Possible values: Boolean ( *true* / *false* , *yes* / *no*, *on* / *off*, *1* / *0* ). if value is *true*, *on*, *yes*, *1* it is true, else its false |
| *min* | - | Optional | This attribute is used with *dataType=integer* or *dataType=number*. It is used to specify minimum limit/value of the integer/number. If not specified it is ignored.|
| *max* | - | Optional | This attribute is used with *dataType=integer* or *dataType=number*. It is used to specify maximum limit of the integer/number. If not specified it is ignored.|.

***IMP NOTE: besides all of the above basic and advanced params, all other normal HTML attributes for the `<input type="dropdown">` tag like *style*, *class* etc. can also be passed.

basic example of dropdown is
```php
$formObj->addDropdown([
    'name'=>'continent',
    'label'=>'Continent',
    'mandatory' => 'true',
    'options'=> [
        [ 'value'=>'africa', 'label'=>'Africa' ],
        [ 'value'=>'america', 'label'=>'America' ],
        [ 'value'=>'asia', 'label'=>'Asia' ],
        [ 'value'=>'australia', 'label'=>'Australia' ],
        [ 'value'=>'europe', 'label'=>'Europe' ],
    ]
);
```

####  Specifying the *options*
Firstly its very important to understand that each item in dropdown control is comprised of *value* and *label* (Same is true for *radio* and *checkboxes* types).

As said before, you can pass these groups of value-label pairs in multiple ways
**1. php array**
here you can simply pass the labels and values as an array of strings.
for example lets see this example, it uses a simple 1d array:
```php
$formObj->addDropdown([
    'name'=>'continent',
    'label'=>'Continent',
    'options'=> ['array' => [ 'africa', 'america', 'asia', 'australia', 'europe' ] ],
);
```
When using 1d array each of the array element becomes the value and label as well.
When values and labels are to be different you can use 2d array like:
```php
$formObj->addDropdown([
    'name'=>'continent',
    'label'=>'Continent',
    'mandatory' => 'true',
    'options'=> [
        [ 'value'=>'afr', 'label'=>'Africa' ],
        [ 'value'=>'amr', 'label'=>'America' ],
        [ 'value'=>'asi', 'label'=>'Asia' ],
        [ 'value'=>'aus', 'label'=>'Australia' ],
        [ 'value'=>'eur', 'label'=>'Europe' ],
    ]
);
```

Now there may be cases where the keys of your array are not named *value* and *label*.
In such situations you can tell it the name of *valuefield* and/or *labelfield* like.

```php
//here is your 2d array where you don't have value and label keys
$customArray = [
    [ 'code'=>'afr', 'continent_name'=>'Africa' ],
    [ 'code'=>'amr', 'continent_name'=>'America' ],
    [ 'code'=>'asi', 'continent_name'=>'Asia' ],
    [ 'code'=>'aus', 'continent_name'=>'Australia' ],
    [ 'code'=>'eur', 'continent_name'=>'Europe' ]
];

$formObj->addDropdown([
    'name'=>'continent',
    'label'=>'Continent',
    'mandatory' => 'true',
    'options'=> [
        'array' => [
            'value' => $continentArray, // here we pass your array
            'labelfield' => 'continent_name', // we specify the label field
            'valuefield' => 'code', // we specify the value field
        ]
    ]
);
```

**2. SQL queries:**
You can also load options from sql queries. All you need to do is just pass in the query to run, and the names of label and value fields.
Ofcourse you should make sure you have the db connected and the dbHandle is set in your form object(for php & pdo).
e.g.
Assume that you have a mysql table named 'continents' like

| code | continent_name |
|------|----------------|
| afr  | Africa |
| amr  | America|
| asi  | Asia   |
| aus  | Australia |
| eur  | Europe |

now you can create options from this table as:

```php
$formObj->addDropdown([
    'name'=>'continent',
    'label'=>'Continent',
    'mandatory' => 'true',
    'options' => [
        'query' => [
            'sql' => 'SELECT * FROM continents;', // here we pass the query
            'labelfield' => 'continent_name', // we specify the label field
            'valuefield' => 'code', // we specify the value field
        ]
    ]
);
```
alternatively you can also modify the query as `select code as value, continent_name as label from continents;` and you wont have to set the labelfield and valuefield.


**3. php global variables**
You can also load the options from php global variables(or sub-elements of the element of the globals array) .e.g.

```php
//somewhere in your code you set a global variable
$GLOBAL['location_data']['continents'] = [
    [ 'code'=>'afr', 'continent_name'=>'Africa' ],
    [ 'code'=>'amr', 'continent_name'=>'America' ],
    [ 'code'=>'asi', 'continent_name'=>'Asia' ],
    [ 'code'=>'aus', 'continent_name'=>'Australia' ],
    [ 'code'=>'eur', 'continent_name'=>'Europe' ]
];

$formObj->addDropdown([
    'name'=>'continent',
    'label'=>'Continent',
    'mandatory' => 'true',
    'options' => [
        'phpglobal' => [
            'var' => 'location_data:continents', // here we pass the continents array from location_data array in $GLOBALS vars
            'labelfield' => 'continent_name', // we specify the label field
            'valuefield' => 'code', // we specify the value field
        ]
    ]
);
```


**4. anonymous functions:**
The anonymous functions are useful when defining the form in XML, you can process global variables/presets and create your data from it, like:

```php
$continents = [
    [ 'code'=>'afr', 'continent_name'=>'Africa' ],
    [ 'code'=>'amr', 'continent_name'=>'America' ],
    [ 'code'=>'asi', 'continent_name'=>'Asia' ],
    [ 'code'=>'aus', 'continent_name'=>'Australia' ],
    [ 'code'=>'eur', 'continent_name'=>'Europe' ]
];

$formObj->addDropdown([
    'name'=>'continent',
    'label'=>'Continent',
    'mandatory' => 'true',
    'options' => [
        'create_function' => [
            'args' => '$a'
            'code' => 'foreach($a as $k => $val ){ $a[$k]["final_name"] = $a[$k]['continent_name']."(".$a[$k]['code'].")"; } return $a;', // the function code
            'pass'=> $continents, //we will pass $continents as argument $a to the function we will create
            'labelfield' => 'final_name', // we specify the label field
            'valuefield' => 'code', // we specify the value field
        ]
    ]
);
```

The above use of anonymous function and phpglobals looks very irrelevant in php definition but comes handy in XML where there is no immediate php processing available.


### radio
This element creates a `<input type="radio">` control in the form

**Basic parameters:**
| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*     | Required | name of the radio control. Note that you need not suffix the [] in name for a element with multiple options, X2Form does it automatically.
| *label* | Optional | Label to be displayed for the radio control |
| *description* | Optional | Description, generally displayed after the radio(unless implemented otherwise using templates or custom renderer ) |
| *id* | Optional | unique id for the radio, its actually the id attribute |
| *value* | Optional | String, to be displayed in radio. |
| *prompt* | Optional | String. Some times we may need to display a default text in the radio. For example. we may need to display "Select your country" in the dropdown when no country is selected, in such cases its accomplished using the prompt. |
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event. In case of multiple radio options the events are applied to all of them |
| *options* | Required | This param specifies the options displayed in the radio. There are multiple ways to define options like php array, sql query, php global variable or anonymous functions(lambda functions in php). We will learn these in details below. This parameter is required as without it the radio wont have any options to display. |

Note: options for radio control can be passed exactly same way as the options for *dropdown* control as we have seen above.

**Advanced configuration parameters:**
Configuration attributes are mainly used for form validation or formatting the display

| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *direction* | horizontal | Optional | This parameter specifies the placement of the radio options if there are multiple options, If its set to *vertical* the options are placed one after each other using a <br/> tag else the options are placed horizontal spaced by a space character.|
| *mandatory* | (boolean) false | Optional | Defines whether the element(control) in the form is mandatory or not, by default the fields are NOT mandatory. If the value is  if the value is *true*, *yes*, or *1* then it means the field is mandatory else the field is not mandatory. |
| *dataType* | - | Optional | Specifies the expected data-type of the submitted value of the field. It is used during validation. supported values are: Supported values: *integer*, *number* (decimal numbers), *email* ( see emailCheckDNS also ), *date*, *time*, *datetime*, *color* ( html color format e.g. #123FFF ), *url*, *ip* (accepts both ipv4 and ipv6 addresses), *ipv4* (only IP v4 address), *ipv6* (only IP v6 address) |
| *dataPattern* | - | Optional | Regular Expression to match with the submitted value. It can be used in cases where the dataType is not sufficient itself or if the type/format of expected value is different than the supported dataTypes. If not specified or if its empty or invalid, it is ignored. |
| *emailCheckDNS* | (boolean) false | Optional | This is effective only when passed with *dataType=email* otherwise it is ignored and has no effect. If its true, it will check if the domain of the email exists or not by checking the DNS. So this attribute should NOT be set to true if your web-server is running in offline mode or is not connected to internet. Possible values: Boolean ( *true* / *false* , *yes* / *no*, *on* / *off*, *1* / *0* ). if value is *true*, *on*, *yes*, *1* it is true, else its false |
| *min* | - | Optional | This attribute is used with *dataType=integer* or *dataType=number*. It is used to specify minimum limit/value of the integer/number. If not specified it is ignored.|
| *max* | - | Optional | This attribute is used with *dataType=integer* or *dataType=number*. It is used to specify maximum limit of the integer/number. If not specified it is ignored.|.

***IMP NOTE: besides all of the above basic and advanced params, all other normal HTML attributes for the `<input type="radio">` tag like *style*, *class* etc. can also be passed.
basic example of radio is

```php
$formObj->addRadio([
    'name'=>'gender',
    'label'=>'Gender',
    'mandatory' => 'true',
    'options'=> [
        [ 'value'=>'male', 'label'=>'Male' ],
        [ 'value'=>'female', 'label'=>'Female' ]
    ]
);
```

### checkbox
This element creates a `<input type="checkbox">` control in the form

**Basic parameters:**
| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*  | Required | name of the checkbox control. Note that you need not suffix the [] in name for a element with multiple options, X2Form does it automatically.
| *label* | Optional | Label to be displayed for the checkbox control |
| *description* | Optional | Description, generally displayed after the control(unless implemented otherwise using templates or custom renderer ) |
| *id* | Optional | unique id for the checkbox, its actually the id attribute |
| *value* | Optional | String, to be displayed in checkbox. |
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event. In case of multiple checkbox options the events are applied to all of them |
| *options* | Required | This param specifies the options displayed in the checkbox. There are multiple ways to define options like php array, sql query, php global variable or anonymous functions(lambda functions in php). We will learn these in details below. This parameter is required as without it the checkboxes wont have any options to display. |
Note: options for checkbox control can be passed exactly same way as the options for *dropdown* control as we have seen above.

**Advanced configuration parameters:**
Configuration attributes are mainly used for form validation or formatting the display

| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *direction* | horizontal | Optional | This parameter specifies the placement of the checkbox options if there are multiple options, If its set to *vertical* the options are placed one after each other using a <br/> tag else the options are placed horizontal spaced by a space character.|
| *mandatory* | (boolean) false | Optional | Defines whether the element(control) in the form is mandatory or not, by default the fields are NOT mandatory. If the value is  if the value is *true*, *yes*, or *1* then it means the field is mandatory else the field is not mandatory. |
| *dataType* | - | Optional | Specifies the expected data-type of the submitted value of the field. It is used during validation. supported values are: Supported values: *integer*, *number* (decimal numbers), *email* ( see emailCheckDNS also ), *date*, *time*, *datetime*, *color* ( html color format e.g. #123FFF ), *url*, *ip* (accepts both ipv4 and ipv6 addresses), *ipv4* (only IP v4 address), *ipv6* (only IP v6 address) |
| *dataPattern* | - | Optional | Regular Expression to match with the submitted value. It can be used in cases where the dataType is not sufficient itself or if the type/format of expected value is different than the supported dataTypes. If not specified or if its empty or invalid, it is ignored. |
| *emailCheckDNS* | (boolean) false | Optional | This is effective only when passed with *dataType=email* otherwise it is ignored and has no effect. If its true, it will check if the domain of the email exists or not by checking the DNS. So this attribute should NOT be set to true if your web-server is running in offline mode or is not connected to internet. Possible values: Boolean ( *true* / *false* , *yes* / *no*, *on* / *off*, *1* / *0* ). if value is *true*, *on*, *yes*, *1* it is true, else its false |
| *min* | - | Optional | This attribute is used with *dataType=integer* or *dataType=number*. It is used to specify minimum limit/value of the integer/number. If not specified it is ignored.|
| *max* | - | Optional | This attribute is used with *dataType=integer* or *dataType=number*. It is used to specify maximum limit of the integer/number. If not specified it is ignored.|.

***IMP NOTE: besides all of the above basic and advanced params, all other normal HTML attributes for the `<input type="checkbox">` tag like *style*, *class* etc. can also be passed.

An example of checkbox with multiple options is:
```php
$formObj->addRadio([
    'name'=>'hobbies',
    'label'=>'Select your hobbies',
    'direction' => 'vertical',
    'options'=> [
        [ 'value'=>'sports', 'label'=>'Sports' ],
        [ 'value'=>'music', 'label'=>'Listening music' ],
        [ 'value'=>'painting', 'label'=>'Drawing & Painting' ],
        [ 'value'=>'video-games', 'label'=>'Video Games' ]
    ]
);
```

An example of checkbox with single option is:
```php
$formObj->addRadio([
    'name'=>'newsletter',
    'label'=>'Receive monthly newsletter?',
    'direction' => 'vertical',
    'options'=> [
        [ 'value'=>'yes', 'label'=>'Yes' ]
    ]
);
```

### file
This element creates a `<input type="file">` control in the form

**Basic parameters:**
| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*  | Required | name of the file element.
| *label* | Optional | Label to be displayed for the file element |
| *description* | Optional | Description, generally displayed after the control(unless implemented otherwise using templates or custom renderer ) |
| *id*    | Optional | unique id for the file element, its actually the id attribute |
| *value* | Optional | String, It is the default value of the control, the value(filename) will be displayed on right of the control if provided. |
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event.|

**Advanced configuration parameters:**
Configuration attributes are mainly used for form validation or formatting the display

| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *mandatory* | (boolean) false | Optional | Defines whether the element(control) in the form is mandatory or not, by default the fields are NOT mandatory. If the value is  if the value is *true*, *yes*, or *1* then it means the field is mandatory else the field is not mandatory. |
| *directory* | ./ | Optional | this attribute is used to specify the path to directory( with ending / ) where the uploaded files are to be stored. |
| *filePrefix*|  | Optional | a string prefix to be attached to the filename while storing it in the upload directory. by default its empty string, means no prefix is attached by default. |
| *allowMIME* |  | Optional | used to specify MIME types that are allowed, it can be a single value or multiple comma separated values. If not specified, it is ignored and MIME types are not checked. |
| *allowExtensions* |  | Optional | used to specify file extensions(WITHOUT DOT) that are allowed, can be a single value or multiple comma separated values. If not specified, it is ignored and extensions are not checked, so its recommended to specify this. |
| *maxSize*   | 20 | Optional |  maximum size of the file in MB that can be uploaded |
| *ifFileExists* | renamenew | Optional |Specify an action file with EXACTLY SAME NAME is already present in file system. For example. you are uploading a myphoto.jpg as a profile image but a file with same name already exist in the upload directory.  Possible values are: *replace* ( if file already exists, replace it and existing file will be deleted ), *reamenew* ( rename new file which is being uploaded, so that names don't clash. |
| *ifOldFileExists* | renamenew | Optional |Specify an action if a file is already uploaded for this control(means old value exist). This scenario may occur while editing an existing record. for example: you have uploaded myphoto.jpg as a profile image but now you are uploading newphoto.jpg as your profile photo. This variable will specify what to do in such cases. Available values are: *delete* ( will delete all old files ), *replace*( replace old file if it has same name as uploaded file(after applying prefix). ), *renameold* ( rename old file so that names don't clash ) | *renamenew* (rename new file so that the names don't clash. ) |
| *imgWidth* |   | Optional | This attribute is used to specify width in pixels for uploaded image,(if its image type). It will generate validation error if width of uploaded image is not exactly as specified. If not specified it is ignored. |
| *imgHeight* |  | Optional | This attribute is used to specify height in pixels for uploaded image,(if its image type). It will generate validation error if height of uploaded image is not exactly as specified. If not specified it is ignored. |

***IMP NOTE: besides all of the above basic and advanced params, all other normal HTML attributes for the `<input type="file">` tag like *style*, *class* etc. can also be passed.

### hidden
This element creates a `<input type="hidden">` in the form.

**Basic parameters:**
| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*  | Required | name for the label tag.
| *id* | Optional | unique id for the hidden element, its actually the id attribute |
| *value* | Optional | String, to be displayed in checkbox. |

**Advanced configuration parameters:**
Configuration attributes are mainly used for form validation or formatting the display.
| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *mandatory* | (boolean) false | Optional | Defines whether the element(control) in the form is mandatory or not, by default the fields are NOT mandatory. If the value is  if the value is *true*, *yes*, or *1* then it means the field is mandatory else the field is not mandatory. |
| *dataType* | - | Optional | Specifies the expected data-type of the submitted value of the field. It is used during validation. supported values are: Supported values: *integer*, *number* (decimal numbers), *email* ( see emailCheckDNS also ), *date*, *time*, *datetime*, *color* ( html color format e.g. #123FFF ), *url*, *ip* (accepts both ipv4 and ipv6 addresses), *ipv4* (only IP v4 address), *ipv6* (only IP v6 address) |
| *dataPattern* | - | Optional | Regular Expression to match with the submitted value. It can be used in cases where the dataType is not sufficient itself or if the type/format of expected value is different than the supported dataTypes. If not specified or if its empty or invalid, it is ignored. |
| *emailCheckDNS* | (boolean) false | Optional | This is effective only when passed with *dataType=email* otherwise it is ignored and has no effect. If its true, it will check if the domain of the email exists or not by checking the DNS. So this attribute should NOT be set to true if your web-server is running in offline mode or is not connected to internet. Possible values: Boolean ( *true* / *false* , *yes* / *no*, *on* / *off*, *1* / *0* ). if value is *true*, *on*, *yes*, *1* it is true, else its false |
| *min* | - | Optional | This attribute is used with *dataType=integer* or *dataType=number*. It is used to specify minimum limit/value of the integer/number. If not specified it is ignored.|
| *max* | - | Optional | This attribute is used with *dataType=integer* or *dataType=number*. It is used to specify maximum limit of the integer/number. If not specified it is ignored.|.

```php
$form->addHidden([
    'name'=>'product_id',
    'value'=> 3,
]);
```

### label
This element creates a `<label>` in the form.

**Basic parameters:**
| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*  | Required | name for the label tag.
| *id* | Optional | unique id for the label, its actually the id attribute |
| *value* | Optional | String, to be displayed in checkbox. |
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event.|

***IMP NOTE: besides all of the above params, all other normal HTML attributes for the `<label>` tag like *style*, *class* etc. can also be passed.

```php
$form->addLabel([
    'name'=>'LABEL1',
    'value'=>'How many matches have you played?',
]);
```


### button
This element creates a `<input type="button">` in the form.

**Basic parameters:**
| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*  | Required | name for the button.
| *id* | Optional | unique id for the button, its actually the id attribute |
| *value* | Optional | String, to be displayed on the button. |
| *label* | Optional | Label to be displayed before the button. Generally not required for a button, but just in case. |
| *description* | Optional | Description, generally displayed after the control. Generally not required for a button, but just in case.  |
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event.|

***IMP NOTE: besides all of the above params, all other normal HTML attributes for the `<input type="button">` tag like *style*, *class* etc. can also be passed.

```php
$form->addButton([
    'name'=>'button1',
    'value'=>'Calculate',
    'class' => 'btn btn-secondary' //these bootstrap classes are optional
]);
```

### submit
This element creates a form submit button `<input type="submit">` in the form.

**Basic parameters:**
| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*  | Required | name for the button.
| *id* | Optional | unique id for the button, its actually the id attribute |
| *value* | Optional | String, to be displayed on the button. |
| *label* | Optional | Label to be displayed before the button. Generally not required for a button, but just in case. |
| *description* | Optional | Description, generally displayed after the control. Generally not required for a button, but just in case.  |
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event.|

***IMP NOTE: besides all of the above params, all other normal HTML attributes for the `<input type="button">` tag like *style*, *class* etc. can also be passed.
```php
$form->addSubmit([
    'name'=>'button2',
    'value'=>'Send',
]);
```

### reset
This element creates a form reset button `<input type="reset">` in the form.

**Basic parameters:**
| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*  | Required | name for the button.
| *id* | Optional | unique id for the button, its actually the id attribute |
| *value* | Optional | String, to be displayed on the button. |
| *label* | Optional | Label to be displayed before the button. Generally not required for a button, but just in case. |
| *description* | Optional | Description, generally displayed after the control. Generally not required for a button, but just in case.  |
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event.|

***IMP NOTE: besides all of the above params, all other normal HTML attributes for the `<input type="button">` tag like *style*, *class* etc. can also be passed.
```php
$form->addReset([
    'name'=>'button3',
    'value'=>'Reset',
]);
```

### image
This element creates a form reset button `<input type="reset">` in the form.

**Basic parameters:**
| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*  | Required | name for the button.
| *id* | Optional | unique id for the button, its actually the id attribute |
| *value* | Optional | String, to be displayed on the button. |
| *label* | Optional | Label to be displayed before the button. Generally not required for a button, but just in case. |
| *description* | Optional | Description, generally displayed after the control. Generally not required for a button, but just in case.  |
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event.|

***IMP NOTE: besides all of the above params, all other normal HTML attributes for the `<input type="button">` tag like *style*, *class* etc. can also be passed.
```php
$form->addReset([
    'name'=>'button3',
    'value'=>'Reset',
]);
```


### captcha
This element inserts captcha in the form.
By default this element uses an external package (Multi Captcha)[http://github.com/sameer-shelavale/multi-captcha] for rendering captcha.

**Basic parameters:**
| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*  | Required | name for the captcha element. Please note this name is not used as the input field name for captcha. The is used to refer to the element in php only. |
| *value* | Optional | String, to be displayed on the button. |
| *secret* | Required |Your secret code for encryption and decryption of the form. It is recommended that you use different codes for each different web form.
| *life* | Optional | total number of hours the generated captcha will be valid. If you set it to 2, then after 2 hours the validate() function will return false even if you enter the correct code. Basically it means the user is expected to submit the form within these many hours after opening the form.
| *customFieldName* | Optional | a custom name for the captcha answer field. If not provided it will use encrypted random name for the field. *Note: Recaptcha type does not honor this parameter.*
| *options* | Optional | array with type of captcha/s that can be rendered as keys and their configurations as value array. If we pass more than one captcha type with it's configuration, it will randomly display one type of captcha from the supplied types. For details see configuration details of each type please refer [Multicaptcha documentation] (https://github.com/sameer-shelavale/multi-captcha#options)
| *refreshUrl* | Optional | url from which we will GET the new captcha using AJAX. If not provided refresh button will not be displayed. Also note that this feature is useful mainly with image, gif, ascii and math captcha.
| *helpUrl* | Optional | url which will provide help related to the captcha type. This url will open in new tab/window.

***IMP NOTE: The label & description are automatically prepared for this field so you need not pass them, they will be ignored if you pass them.

```php
$form->addCaptcha([
    'secret'=>'secret-code-for-this-form',
    'options'=> array(
        'math'=>[
            'level'=>4
        ],
        'gif' => [
            'maxCodeLength' => 6,
            'width'=>180,
            'height'=>60,
            'totalFrames'=>50,
            'delay'=>20
        ]
    ),
    'refreshUrl'=>'your-captcha-refresh-url.php?captcha=refresh',
    'helpUrl'=>'http://github.com/sameer-shelavale/multi-captcha'

]);
```

### an example of form made with add* functions
```php
$form = new \X2Form\Form(
    'ContactUs',
    [
        'action' => 'contact-us.php',
        'method' => 'post'
    ]
);
$form->addText([
    'name' => 'full_name',
    'label' => 'Your Name'
]);
$form->addText([
    'name' => 'email',
    'label' => 'Email',
    'mandatory' => true,
    'datatype' => 'email'
]);
$form->addTextarea([
    'name' => 'message',
    'label' => 'Message',
    'mandatory' => true
]);
$form->addRadio([
    'name' => 'gender',
    'label' => 'Gender',
    'options' => [ 'Male', 'Female' ]
]);
$form->addCaptcha([
    'name' => 'captcha',
    'secret'=>'contact-us-form-secret-blahblah',
    'options' => [
        'gif' => [
            'maxCodeLength' => 6,
            'width'=>200,
            'height'=>80
        ]
     ]
]);
$form->addSubmit([
    'name' => 'submit',
    'value' => 'Send'
];
$form->finalize(); //prepares the form for rendering, processing, validation etc.

```


### specifying elements together in form constructor
We can also club the elements together in the form constructor param *elements*.
Lets see how to do it for a simple contact us form.
```php
$form = new \X2Form\Form(
    'ContactUs',
    [
        'action' => 'contact-us.php',
        'method' => 'post'
        'elements' => [
            [
                'type' => 'text',
                'name' => 'full_name',
                'label' => 'Your Name'
            ],
            [
                'type' => 'text',
                'name' => 'email',
                'label' => 'Email',
                'mandatory' => true,
                'datatype' => 'email'
            ],
            [
                'type' => 'textarea',
                'name' => 'message',
                'label' => 'Message',
                'mandatory' => true
            ],
            [
                'type' => 'radio',
                'name' => 'gender',
                'label' => 'Gender',
                'options' => [ 'Male', 'Female' ]
            ],
            [
                'type' => 'captcha',
                'name' => 'captcha',
                'secret'=>'contact-us-form-secret-blahblah',
                'options' => [
                    'gif' => [
                        'maxCodeLength' => 6,
                        'width'=>200,
                        'height'=>80
                    ]
                 ]
            ],
            [
                'type' => 'submit',
                'name' => 'submit',
                'value' => 'Send'
            ],
        ]
    ]
);
$form->finalize(); //prepares the form for rendering, processing, validation etc.
```

Note: It is necessary to run the finalize() function after you are done with adding/updating the form fields.

## Rendering the form.
Once you *finalize* the form, everything else is very easy.
To display the form all you need to do is
```php
echo $form->render();
```
Remember that the render() function returns string, so you need to *echo* it.

## processing & validating the form
After you *finalize* the form, you can *process submission* or *validate* it.

There is slight difference between the processSubmission() and validate() functions.
The validate() function only validates the data and return boolean value as result and does nothing else,
while the processSubmission() function validates the data as well as handles the file uploads also,i.e. it also moves the uploaded files to their target directories.
It returns a detailed array log with *status*, *message* and array of error fields and their respective error messages.

```php
    if( $_POST['submit'] == "Submit" ){
        if( logg_ok( $form->processSubmission( $_POST ) ){
            echo "Your data is submitted successfully!";
        }else{
            //display form again with submitted data populated in it and highlighted error fields
            echo '<span class="error">'.$form->errorString.'</span>';
            echo $form->render();
        }
    }else{
        //display form
        echo $form->render();
    }
```

```php
    if( $_POST['submit'] == "Submit" ){
        if( $form->validate( $_POST ) ){
            //do extra/special validations and server side processing and save data etc.
        }else{
            //display form again with submitted data populated in it and highlighted error fields
            echo '<span class="error">'.$form->errorString.'</span>';
            echo $form->render();
        }
    }else{
        //display form
        echo $form->render();
    }
```

### function processSubmission()

'function processSubmission( $postedData, $oldData, $cancelUploadsOnError = true )'

Almost all of the processing of form submission can be done with this one function.

**Parameters:**

*$postedData* - it is the posted data as associative array, where array keys are the name of fields, so most of the times you will be passing $_POST as $postedData

*$oldData* - You will need to pass this while editing existing records or data. It is mainly used for file handling and form validation.
For example. if you have a form field PROFILE_PHOTO which is a mandatory field. Now even if the user don't upload new file for PROFILE_PHOTO, it should not throw error if the photo is uploaded previously.
X2Form checks if the file is uploaded/exists in $oldData, if it exists it will not throw validation error.

*$cancelUploadsOnError* - this parameter states whether the uploaded files should be deleted on error or not.
Certainly there will be cases where you are uploading multiple files and one of the upload fails due to problem in upload or moving or validation. In such cases we may need to rollback the changes in file system.
If value of this parameter is true then X2Forms rolls back the changes in file system gracefully.
Note. X2Forms backs up all the changes it is making in file-system, it creates backup files which it uses later for rollback.

*Return Value* - returns an array of *result*, *code*, *message*, *errorFields*
    *result* - it indicates whether the operation was a Success or Failure. so the value is either 'Success' or 'Failure'
    *code* - it is a short errorcode
    *message* - Description of the result of operation. you can generally display it to user as well.
    *errorFields* - Associative array of fields which did not pass validation, where array keys are the name of fields.


### function validate()

'function validate( $postedData, $oldData)'

This function is used internally by the processSubmission() function.

**Parameters:**

*$postedData* - it is the posted data as associative array, where array keys are the name of fields, so most of the times you will be passing $_POST as $postedData

*$oldData* - You will need to pass this while editing existing records or data. It is mainly used for file handling and form validation.
For example. if you have a form field PROFILE_PHOTO which is a mandatory field. Now even if the user don't upload new file for PROFILE_PHOTO, it should not throw error if the photo is uploaded previously.
X2Form checks if the file is uploaded/exists in $oldData, if it exists it will not throw validation error.

*Return Value* - returns (boolean) *true* on successful validation or *false*

Note: Whatever $postedData you pass to the processSubmission() and validate() functions, it will be populated in the form.
Also the functions mark fields with errors by setting the *errorString* property on that field(element) and these fields will have an extra css class *errorfield*.
It also sets the '$form->errorString' which contains summery of the errors occurred during validation.

## Refreshing elements using ajax

## Rendering the form & form element using frameworks like bootstrap
By default X2Form renders form in tabular format, means using the `<table>` `<tr>` and `<td>` tags.
You can also render it using bootstrap by setting the renderer to a bootstrap renderer object, yes that is all you need.
for example:
```php
$form = new \X2Form\Form(
    'MyFormName',
    [
        'action' => 'index.php',
        'method' => 'post',
        'renderer' => new X2Form\Renderers\Bootstrap\Renderer() //this is all you need to render in bootstrap
    ]
);
```

Note: Right now it supports *table* & *bootstrap* renderers only; we are planning to add jqueryui and angular renderers in future.
You are however free to extend the current renderers or implement your own.
Remember renderer MUST implement *X2Form\Interfaces\Renderer* interface.

## Customising the form layout and positioning of elements using templates
There are times when you don't want the default two column vertical layout of the form and you want more control on positioning of the individual fields in the form.
Even the buttons come up one below another.(May be I should add a button-group type in future)

In such situations the templates come really handy, using templates you can organize the form and position the elements as you want.

A X2Form template is a normal html/php file, all the data within the `<body>` and `</body>` tags is used as form template,
the html `<head>` part is discarded, this is allows you to use any html editor to quickly make the template.

To place a Form Element in the template, just write text [ELEMENTNAME] in that place, here ELEMENTNAME is the *name* of Element.

After rendering the [ELEMENTNAME] will be replaced by actual html for the element.

So, for FIRST_NAME in, we can put in [FIRST_NAME]

Similarly, to place the label for element , put in [FIRST_NAME_label]  notice the suffix _label here and to show the description use [FIRST_NAME_description].

While doing all this remember that you MUST NOT specify the `<FORM>` tag in the template the renderer places `<form>` tag around the template data automatically.

##

*Special thanks to JetBrains(http://www.jetbrains.com) for granting free license of jetBrains PHPStorm IDE for this project and their relentless support to the open-source community.