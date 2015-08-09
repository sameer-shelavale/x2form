x2form
======

X2Form is a form generator/builder developed for easy creation & maintenance of web forms.

The X2Form architecture separates the definition of the form elements, rendering of the elements, positioning of the elements(layout/templates) and processing/validation of the form.
This makes it very easy to update and maintain the forms in long term adding and removing more fields is extremely easy.
 
##Main features:
1. It can create web forms from
    a. Laravel/Eloquent models or objects.
    b. mysql tables.
    c. pure PHP calls by creating a X2Form\Form object and adding elements to it using the add* methods in Form class.
    d. XML definition of the form.
2. It can read values of dropdowns, checkboxes and radio from PHP functions, PHP Closures, PHP Global variables MYSQL queries.
3. Supports HTML/PHP template for customizing the form layout
4. Can handle file uploads, it can also rollback the file system changes if something goes wrong.
5. It can do validation of the form values depending on the 'datatype' or 'datapattern'(using regular expressions)
6. Values to be  pre-populated in the form can be passed in as array
7. Multi-Language support, you can define labels, tooltips, description as well as error messages in multiple languages.
   Thus making your form to render properly in multiple languages
8. Easily extensible. Adding extra types of form elements or add more types of loaders, renderers and templates is easy.
9. Clean name-spaced code
10.Easy installation using Composer.


## Installation & Configuration:

### Using Composer

#### Command Line
You can install X2Form using Composer by running
```
composer require sameer-shelavale/multi-captcha
```

#### composer.json
Alternatively, you can add it directly in your composer.json file in require block

```
{
    "require": {
        "sameer-shelavale/x2form": "2.0.*"
    }
}
```

and then run `composer update`

### PHP Include
You can also download the zip/rar archive and extract it and copy the folder to appropriate location in your project folder.
And then include the autoload.php in your code

```
include_once( 'PATH-TO-X2FORM-FOLDER/src/autoload.php' );
```

## Supported form controls/elements
X2Form supports following HTML controls:
1. text
2. textarea
3. checkbox
4. radio
5. dropdown
6. file
7. label
8. button ( i.e. `<input type="button" >` )
9. submit (i.e. `<input type="submit" >`)
10. reset (i.e. `<input type="reset" >` )
11. hidden (i.e. `<input type="hidden" >` )
12. image (i.e. `<input type="image" >` )
13. captcha

## Usage
### Initialize
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

### Form controls/elements
You can use constructor param named *elements* or after you initialize the form object you can use add* functions or the load method to load the elements from ORM objects or XML files or XML string or SimpleXML Object.
First we will see the available element types and how to add them in the form.


##### text
This element creates a *textbox* or `<input type="text">` control in the form

Basic parameters are:
-------------------------

| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *name*     | Required | name of the textbox
| *label* | Optional | Label to be displayed for the textbox |
| *description* | Optional | Description, generally displayed after the textbox(unless implemented otherwise using templates or custom renderer ) |
| *id* | Optional | unique id for the textbox, its actually the id attribute |
| *value* | Optional | String, to be displayed in textbox.
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event |

Advanced configuration parameters:
-------------------------
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

##### textarea
This element creates a `<textarea>` control in the form

Basic parameters are:
-------------------------

| Param | Default Value | Required | Description |
| ----- | ------------- | -------- | ----------- |
| *name*     | Required | name of the textarea
| *label* | Optional | Label to be displayed for the textarea |
| *description* | Optional | Description, generally displayed after/below the textarea(unless implemented otherwise using templates or custom renderer ) |
| *id* | Optional | unique id for the textarea, its actually the id attribute |
| *value* | Optional | text to be displayed in textarea.
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event |

Advanced configuration parameters:
-------------------------
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


##### dropdown
This element creates a `<select>` control in the form

###### Basic parameters are:

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

###### Advanced configuration parameters:
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

######  Specifying the *options*
Firstly its very important to understand that each item in dropdown control is comprised of *value* and *label* (Same is true for *radio* and *checkboxes* types).

As said before, you can pass these groups of value-label pairs in multiple ways
######  1. php array
here you can simply pass the labels and values as an array of strings.
for example lets see this example, it uses a simple 1d array:
```php
$formObj->addDropdown([
    'name'=>'continent',
    'label'=>'Continent',
    'options'=> [ 'africa', 'america', 'asia', 'australia', 'europe' ],
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

###### 2. SQL queries:
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


###### 3. php global variables
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


###### 4. anonymous functions:
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


##### radio
This element creates a `<input type="radio">` control in the form

###### Basic parameters are:

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

###### Advanced configuration parameters:
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

##### checkbox
This element creates a `<input type="checkbox">` control in the form

###### Basic parameters are:

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

###### Advanced configuration parameters:
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

##### file
This element creates a `<input type="file">` control in the form

###### Basic parameters are:

| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*  | Required | name of the file element.
| *label* | Optional | Label to be displayed for the file element |
| *description* | Optional | Description, generally displayed after the control(unless implemented otherwise using templates or custom renderer ) |
| *id*    | Optional | unique id for the file element, its actually the id attribute |
| *value* | Optional | String, It is the default value of the control, the value(filename) will be displayed on right of the control if provided. |
| *events* | Optional | Array of events where each key is name of event like *onclick* etc. and the value is the javascript code to be executed for that event.|

###### Advanced configuration parameters:
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

##### hidden
This element creates a `<input type="hidden">` in the form.

###### Basic parameters are:

| Param | Required | Description |
| ----- | -------- | ----------- |
| *name*  | Required | name for the label tag.
| *id* | Optional | unique id for the hidden element, its actually the id attribute |
| *value* | Optional | String, to be displayed in checkbox. |

###### Advanced configuration parameters:
Configuration attributes are mainly used for form validation or formatting the display

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


##### label
This element creates a `<label>` in the form.

###### Basic parameters are:

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


##### button
This element creates a `<input type="button">` in the form.

###### Basic parameters are:

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

##### submit
This element creates a form submit button `<input type="submit">` in the form.

###### Basic parameters are:

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

##### reset
This element creates a form reset button `<input type="reset">` in the form.

###### Basic parameters are:

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

##### image
This element creates a form reset button `<input type="reset">` in the form.

###### Basic parameters are:

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

We can also club the elements together in the constructor param *elements*.

*Special thanks to JetBrains(http://www.jetbrains.com) for granting free license of jetBrains PHPStorm IDE for this project and their relentless support to the open-source community.