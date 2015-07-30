<?php
/*******************************************************************************************************
 * class X2Form
 * X2Form is an architecture for effective creation & management of web forms.
 * The HTML forms are defined in XML(instead of normal HTML) to allow adding extra attributes which induce additional functionality.
 * The PHP class(X2Form) read the XML definition and render the form as well as process & validate submitted form.
 *
 * This architecture effectively separates of the  processing of form, definition of the form elements, rendering of the elements and positioning of the elements(layout)
 *
 * Main features:
 * 		1. Create HTML forms from XML file/string(predefined format)
 * 		2. Can generate forms using only pure PHP calls as well(without XML files)
 * 	>>>>3. It can read values of dropdowns, checkboxes and radio from PHP functions, PHP Closures, PHP Global variables MYSQL queries.
 * 		3. Supports HTML/PHP template for customizing of the form layout
 * 		4. Can handle file uploads, it can also rollback the file system changes if something goes wrong
 * 		5. This class can do validation of the form values depending on the 'datatype' or 'datapattern'(using regular expressions)
 * 		6. Values to be  pre-populated in the form can be passes in as array
 * 		7. Multi-Language support, you can define labels, tooltips, description as well as error messages in multiple languages.
 * 		   Thus making your form to render properly in multiple languages
 *
 *
 *
 * Author : Sameer Shelavale
 * Email  : samiirds@gmail.com, sameer@techrevol.com, sameer@possible.in
 * Author website: http://techrevol.com, http://possible.in
 * Phone  : +91 9890103122
 * License: AGPL3, You should keep Package name, Class name, Author name, Email and website credits.
 * 			http://www.gnu.org/licenses/agpl-3.0.html
 * 			For other type of licenses please contact the author.
 * PHP Version: Tested on PHP 5.2.2 & 5.3.10
 * Dependencies : X2FormElement.php, class.dbhelper.php
 * Copyrights (C) 2012-2013 Sameer Shelavale
 *******************************************************************************************************/
namespace X2Form;
use X2Form\Renderers;
use X2Form\Loaders;
use ArrayObject;
use SimpleXMLElement;

require_once ( ltrim( __DIR__, '/'). '/helpers/dbhelper.php' );
require_once ( ltrim( __DIR__, '/'). '/helpers/logger.php' );

//Auto load the required php classes
spl_autoload_register( function($className){
    $curDir = ltrim( __DIR__, '/');
    //$cls = array_pop( explode('\\', $className) );

    if( preg_match( '/X2Form\\\Renderers\\\(bootstrap|table|div|jqueryui|table)\\\(.+)$/i', $className, $matches ) ){
        $cls = ltrim( strtolower( preg_replace( '/([A-Z])/', '_$1',$matches[2])), '_');
        require_once( $curDir.'/renderers/'. strtolower($matches[1]).'/'.$cls.'.php');
    }elseif( preg_match( '/X2Form\\\Loaders\\\(.+)$/i', $className, $matches ) ){
        $cls = ltrim( strtolower( preg_replace( '/([A-Z])/', '_$1',$matches[1])), '_');
        require_once( $curDir.'/loaders/'. $cls.'.php');
    }elseif( preg_match( '/X2Form\\\Interfaces\\\(.+)$/i', $className, $matches ) ){
        $cls = ltrim( strtolower( preg_replace( '/([A-Z])/', '_$1',$matches[1])), '_');
        require_once( $curDir.'/interfaces/'. $cls.'.php');
    }elseif( preg_match( '/X2Form\\\(.+)$/i', $className, $matches ) ){
        $cls = ltrim( strtolower( preg_replace( '/([A-Z])/', '_$1',$matches[1])), '_');
        require_once( $curDir.'/'.$cls.'.php');
    }
});

class Form{

    var $index = false; // index in form collection 
    // this will be used if this is form is part of a collection

    //variables initialized by constructor
    var $name;					// name for FORM tag, this should also be the name of xml file for this form
    var $dbType = "php";		// framework to be used for running queries, possible values 'php', 'php-pdo', 'joomla'
    var $dbHandle = false;		// database handle will be required for pdo and some other frameworks
    var $template;				// template to be used for generating form
    var $parent = false;
    var $renderer;
    var $loader;
    var $excludeFields;
    var $language = false;

    //variables storing form and element details
    var $id;				// id for FORM tag
    var $attributes;		// attributes to output in FORM tag
    var $elements;			// this will be used internally to store form elements and their details

    //variables for storing raw information about form
    var $xml;				//
    var $xmlfile;			//

    var $extraCode = '';
    var $errorString = '';
    var $errorFields = array();
    var $isLoaded = false;

    var $callBack = array(); //this array will hold all the callback functions/closures

    var $inbuiltRenderers = array(
        'table'     => '\X2Form\Renderers\Table\Renderer',
        'div'       => '\X2Form\Renderers\Div\Renderer',
        'bootstrap' => '\X2Form\Renderers\Bootstrap\Renderer',
        'jqueryui'  => '\X2Form\Renderers\Jqueryui\Renderer'
    );

    var $ready = false;

    public function __construct( $name = '', $params  ){
        //$template=false, $lang=false, $dbTyp = 'php', &$dbHnd=false, $idx = false, &$parentForm = false
        $this->name = $name;

        //params
        $this->index = (isset( $params['index']))?$params['index']: $this->index = false;
        $this->parent = (isset( $params['parent']))?$params['parent']: $this->parent = false;
        $this->template = (isset( $params['template']))?$params['template']: $this->template = false;
        $this->language = (isset( $params['language']))?$params['language']: $this->language = false;
        $this->dbType = (isset( $params['dbType']))?$params['dbType']: $this->dbType = 'php';
        $this->dbHandle = (isset( $params['dbHandle']))?$params['dbHandle']: $this->dbHandle = false;

        if( isset( $params['renderer']) ){
            $this->renderer = $params['renderer'];
        }else{
            //render using tables by default
            $this->renderer = new Renderers\Table\Renderer();
        }

        if( isset( $params['loader']) && is_object( $params['loader']) && is_subclass_of( $params['loader'], 'X2Form\Interfaces\Loader'  )  ){
            $this->loader = $params['loader'];
        }else{
            //render using tables by default
            $this->loader = new Loaders\Auto();
        }

        ///initialize variables
        $this->elements = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );
        $this->attributes = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );

        if( isset( $params['exclude'] ) ){
            if( is_array( $params['exclude'] ) ){
                $this->excludeFields = $params['exclude'];
            }elseif( is_string( $params['exclude'] ) ){
                $this->excludeFields = [ $params['exclude'] ];
            }else{
                //ignore exclude
            }
        }

        if( isset( $params['from']) ){
            $exclude = [];
            if( isset( $params['exclude'] ) ){
                $exclude = $params['exclude'];
            }
            $this->load( $params['from'], $exclude );
        }

    }

    function load( $from, $exclude=[] ){
        if( method_exists( $this->loader, 'load' ) ){
            $log = $this->loader->load( $this, $from, $exclude );
            if( $log['result'] == 'Failure' ){
                $this->isLoaded = false;
                $this->errorString = 'Unable to load form fields';
            }
        }else{
            $log = Logg( 'Failure', '', 'load() function missing in loader object.' );
        }
        return $log;
    }


    /**************************************************************************
     * PHP Magic Method - __sleep()
     * 		This is used to define the properties to save during serialization.
     * 		This is called during serialization in the getDeepClone() method
     **************************************************************************/
    public function __sleep(){
        return array( 'index', 'name', 'dbType', 'template', 'id', 'attributes',
            'elements', 'language', 'xml', 'xmlfile', 'extraCode',
            'errorString', 'errorFields', 'isLoaded', 'callBack' );
    }


    /*****************************************************************************
     * function deepClone()
     * 		This function returns a deep clone of the X2Form object
     * 		This method is used in X2Form\Collection::setValues() method for
     * 		creating more sub-forms of the prototype-form.
     * returns:
     * 		a deep clone of the current object
     ****************************************************************************/
    public function deepClone(){
        $dbh = $this->dbHandle;

        $obj = unserialize( serialize( $this ));
        $obj->parent = &$this->parent;
        $obj->dbHandle = $dbh;
        foreach( $obj->elements as $i=>$elem ){
            $obj->elements[$i]->parent = &$obj;
            $obj->elements[$i]->dbHandle = &$dbh;
        }
        return $obj;
    }


    /********************************************************************************
     * function createNode()
     *		This function create a X2Form\Collection or X2Form\Element object from
     *		passed simpleXMLElement object
     * parameters:
     * 		$elem - a SimpleXMLElement object
     * returns:
     * 		X2Form\Collection or X2Form\Element object
     ********************************************************************************/
    function createNode( $elem ){

        $prop = array();
        $children = array(); //in case the element is a 'group'

        //now find the $prop, $conf and $attr
        foreach( $elem->attributes() as $k => $v ){
            $prop[ strtolower( "$k" ) ] = "$v";
        }



        $opt = array();

        if( isset( $prop['type'] ) && $prop['type'] =="collection" || strtolower( $elem->getName() ) == 'collection' ){

            $prop['type'] = 'collection'; //force type=group
            $result = new Collection(
                $prop['name'],
                [
                    'from'=> $elem,
                    'language' => $this->language,
                    'dbType' => $this->dbType,
                    'dbHandle' => $this->dbHandle,
                    'parent' => &$this
                ]
            );

        }else{

            //find the values of different attributes in different languages
            if( $elem->languages ){
                foreach( $elem->languages->children() as $lang ){

                    foreach( $lang->children() as $langSetting ){
                        $prop['languages'][ (string)$lang->attributes()->name ][$langSetting->getName()] = (string)$langSetting;
                    }
                }
            }


            //find options of the element,
            //options are to be mentioned for dropdowns, multiple checkboxes and multiple radio boxes

            if( $prop['type'] =="textarea" && "$elem" != '' ){
                $prop['value'] = (string)$elem;
            }

            if( $elem->options && isset( $prop['type'] ) && $prop['type'] =="captcha" ){
                //if options are passed for CAPTCHA in xml itself as children of the element tag
                if( $elem->options->option ){
                    foreach( $elem->options->option as $o ){
                        $optAtr = array();
                        foreach( $o->attributes() as $k => $v ){
                            $k = "$k";
                            $optAtr[ $k ] = "$v";
                        }
                        if( isset( $optAtr['type'] ) ){
                            $opt[ $optAtr['type'] ] = $optAtr;
                        }
                    }
                }
            }elseif( $elem->options && $elem->options->option ){
                //if options are passed in xml itself as children of the element tag
                foreach( $elem->options->option as $o ){
                    $optAtr = array();
                    foreach( $o->attributes() as $k => $v ){
                        $k = strtolower( "$k" );
                        if( $k == 'value' || $k == 'label' ){
                            $optAtr[ $k ] = "$v";
                        }
                    }
                    //check if only value is given
                    if( $optAtr['label'] || $optAtr['value'] ){
                        if( !isset($optAtr['label']) || $optAtr['label'] === false || $optAtr['label'] === null){
                            $optAtr['label'] = $optAtr['value'];
                        }elseif( !isset( $optAtr['value'] ) || $optAtr['value'] === false || $optAtr['value'] === null ){
                            $optAtr['value'] = $optAtr['label'];
                        }

                    }
                    $opt[] = $optAtr;
                }
            }elseif( $elem->options && $elem->options->query ){
                //options are to be fetched from result of a passed query
                //the query is passed by 'query' tag which is child of options tag
                $opt['query'] = "{$elem->options->query}";

                //find the valuefield and labelfield
                //these will be used to pick up values from the query results
                foreach( $elem->options->query->attributes() as $k => $v ){
                    $k = strtolower( "$k" );
                    if( strtolower( $k ) == 'valuefield' ){
                        $opt['valuefield'] = "$v";
                    }
                    if( strtolower( $k ) == 'labelfield' ){
                        $opt['labelfield'] = "$v";
                    }
                }
            }elseif( $elem->options && $elem->options->create_function ){
                //options are to be fetched from result of a passed query
                //the query is passed by 'query' tag which is child of options tag
                $opt['create_function'] = "{$elem->options->create_function}";

                //find the valuefield and labelfield
                //these will be used to pick up values from the query results
                foreach( $elem->options->create_function->attributes() as $k => $v ){
                    $k = strtolower( "$k" );
                    $opt[$k] = "$v";
                }
            }elseif( $elem->options && $elem->options->phpglobal ){
                //options are to be fetched from result of a passed query
                //the query is passed by 'query' tag which is child of options tag
                $opt['phpglobal'] = true;

                //find the valuefield and labelfield
                //these will be used to pick up values from the query results
                foreach( $elem->options->phpglobal->attributes() as $k => $v ){
                    $k = strtolower( "$k" );
                    $opt[$k] = "$v";
                }
            }

            //find events if any
            //events are defined in 'event' tag which is child of 'events', which in turn is child of the element tag
            $events = array();
            if( $elem->events->event ){
                foreach( $elem->events->event as $e ){
                    foreach( $e->attributes() as $k => $v ){
                        if( $k == 'type' ){
                            $events[ "$v" ] = "$e";

                        }
                    }
                }
            }

            //so we have now all data required for creating the X2Form\Element object
            $prop[ 'options' ] = $opt;
            $prop[ 'events' ] = $events;
            $prop[ 'dbType' ] = $this->dbType;
            $prop[ 'dbHandle' ] = $this->dbHandle;
            $prop[ 'parent' ] = &$this;

            $result = new Element( $prop['type'], $prop );

        }

        return $result;
    }

    /*****************************************************************************
     * function finalize()
     *      evaluates all queries, php expressions passed for finding datasets for
     *      elements like dropdowns etc. Also computes final output names for fields
     *      It is essential to call this function before rendering or validation
     *****************************************************************************/
    function finalize(){
        if( !$this->ready ){
            foreach( $this->elements as $i => $elem ){
                //set $this as parent of all elements
                //we do this here because we don't want to call function for adding elements
                //nor do we want the user to have pain of specifying the parent when its so obvious
                $this->elements[$i]->parent = $this;
                //now finalize the elment itself
                $this->elements[$i]->finalize();
            }
            $this->ready = true;
        }
    }

    /*****************************************************************************
     * function render()
     * 		renders the HTML Form from for 'this' X2Form\Form object.
     * parameters:
     * 		$addFormTag - true  - rendered form has the <FORM> tag as well
     * 					- false - rendered form does not have the <FORM> tag
     * returns:
     * 		the rendered HTML as string.
     * 		Note that it does'nt send output to screen
     ****************************************************************************/
    function render( $addFormTag=true ){
        //first check if the xml is loaded properly
        if( !$this->isLoaded ){
            return $this->errorString;
        }
        $this->finalize();

        return $this->renderer->render( $this, $addFormTag );
    }



    /*****************************************************************************
     * function render()
     * 		Render a raw HTML template without adding any values and without template file
     * returns:
     * 		the rendered HTML as string.
     * 		Note that it does'nt send output to screen
     ****************************************************************************/
    function renderRawTemplate(){
        if( !$this->isLoaded ){
            return $this->errorString;
        }
        $this->finalize();

        if( $this->template && is_file( $this->template ) ){
            return $this->renderTemplate();
        }

        return $this->renderer->raw( $this );
    }





    /***********************************************************************************
     * function processSubmission()
     * 		This function validates and processes submitted data
     *
     * Parameters:
     * 		$postedData - the submitted data
     * 		$oldData	- old data if any, required only if there are old files
     * 					  if file is uploaded earlier it will not be mandatory
     * 		$rollbackOnError
     * 					- true - rollback uploads and changes to file system if error occurs
     * 					- false - do not rollback uploads & file system changes on error
     *
     ***********************************************************************************/
    function processSubmission( $postedData, $oldData=array(), $rollbackOnError = true ){

        $this->setValues( $postedData );
        $this->storeOldValues( $oldData );

        if( $this->validate( $postedData ) ){
            Logg( 'LOG', 'CODE', 'Submited data has passed validation.' );

            $this->handleFileUploads( $postedData, $oldData );
            return Logg( 'Success', 'S001', 'Form submission successful.' );
        }else{
            if( $rollbackOnError ){
                $this->rollBackFileUploads();
            }
            $log = Logg( 'Failure', 'E001', 'Problem occured while processing submission.' );
            $log['errorFields'] = $this->errorFields;

            return $log;
        }

    }

    /***********************************************************************************
     * function validate()
     * 		This function validates the form data.
     * Returns:
     *		returns true on success, false on failure.
     *		it sets $errorString variable on failure.
     ***********************************************************************************/
    public function validate( ){
        $this->errorString = '';

        foreach( $this->elements as $element ){
            //skip submit buttons and files
            //we will handle files in separate call

            if( $element->type != 'submit' && $element->type != 'button' && $element->type != 'reset' && $element->type != 'label' ){

                $val = $element->validate();
                if( strlen( $val ) > 0 ){
                    $this->errorFields[ $element->name ] = $val;
                    $this->errorString .= $this->errorFields[ $element->name ].'<br/>';
                }
            }
        }


        if( $this->errorString ){
            //clear the filenames
            foreach( $this->elements as $i => $element ){
                if( $element->type == 'file' ){
                    if( $this->elements[$i]->oldValue ){
                        $this->elements[$i]->value = $this->oldValue ;
                    }else{
                        $this->elements[$i]->value = "" ;
                    }
                }
            }
            return false;
        }
        return true;
    }




    /***********************************************************************************
     * function SetValues( $formValues )
     * 		This function populates the form elements with values passed in $formValues.
     * PARAMETERS:
     *		$formValues  - associative array of submitted values(generally $_POST or $_REQUEST )
     ***********************************************************************************/
    public function setValues( $formValues ){
        $this->errorString = '';
        foreach( $this->elements as $element ){
            if( $element instanceOf \X2Form\Collection ){
                $element->setValues( $formValues[ $element->name ] );
            }elseif( $element->type != 'submit' && $element->type != 'button' && isset($formValues[$element->name] ) ){
                $element->value = $formValues[ $element->name ];
            }
        }
        return true;
    }



    /***********************************************************************************
     * function getValues( )
     * 		This function fetches values of form elements as a associative array.
     *
     ***********************************************************************************/
    public function getValues(){
        $values = array();
        foreach( $this->elements as $element ){
            //skip submit buttons and files
            if( $element instanceOf \X2Form\Collection ){
                $values[ $element->name ] = $element->getValues();
            }elseif( !in_array( $element->type, array( 'submit', 'button' ) ) ){
                $values[ $element->name ] = $element->value;
            }
        }
        return $values;
    }



    /***********************************************************************************
     * function storeOldValues( $oldValues )
     * 		This function stores old values for each element.
     * 		These values are required mainly for validating 'file' inputs
     * 		In case a file is already uploaded(in old data) and is not uploaded
     * 		in current form submission then it should not raise validation error.
     * PARAMETERS:
     *		$oldValues  - associative array of old values
     ***********************************************************************************/
    public function storeOldValues( $oldValues ){
        foreach( $this->elements as $element ){
            if( $element->type != 'submit' && $element->type != 'button' && isset( $oldValues[ $element->name ] ) ){
                $element->oldValue = $oldValues[ $element->name ];
            }
        }
    }



    /***********************************************************************************
     * function handleFileUploads()
     * 		This function handles uploaded files for all elements of type=file.
     * 		It is called after validate().
     * 		If an error occurs during handling of a mandatory file, it should cancel uploads.
     ***********************************************************************************/
    public function handleFileUploads(){
        $uploadError = false;
        foreach( $this->elements as $element ){
            if( $element->type == 'file' ){
                if( !$element->handleFileUpload() ){
                    $uploadError = true;
                    break;
                }
            }
        }

        if( $uploadError ){
            $this->rollBackFileUploads();
        }

    }


    /***********************************************************************************
     * function rollBackFileUploads()
     * 		This function cancels all files uploaded.
     * 		if some error occurs during file upload, or form submission, this function
     * 		rolls back all the changes and restores files deleted(backed up and marked for deletion actually)
     ***********************************************************************************/
    public function rollBackFileUploads(){
        foreach( $this->elements as $element ){
            if( $element->type == 'file' ){
                $element->rollBackFileUploads();
            }
        }
    }


    /***********************************************************************************
     * function setErrorFields()
     * 		This function sets error messages on each field provided in input array
     * parameters:
     *		$eFields  - associative array with field names as keys and error messages as values
     *
     ***********************************************************************************/
    public function setErrorFields( $eFields ){
        foreach( $eFields as $eF => $eVal ){
            if( isset( $this->elements[$eF] ) ){
                if( $this->elements[$eF]->type == 'collection' ){
                    $this->elements[$eF]->setErrorFields( $eFields );
                }else{
                    $this->elements[$eF]->errorString = $eVal;
                    $this->errorFields[$eF] = $eVal;
                }
            }
        }
    }


    /***********************************************************************************
     * function reset(  )
     * 		This function populates the form elements with values passed in $formValues.
     * PARAMETERS:
     *		$formValues  - associative array of submitted values(generally $_POST or $_REQUEST )
     ***********************************************************************************/
    public function reset(){
        $this->errorString = '';
        foreach( $this->elements as $element ){
            if( $element->type != 'submit' && $element->type != 'button' && $element->type != 'label' ) {
                $element->value = '';
            }
        }
        return true;
    }

}