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


class Form{

    var $index = false; // index in form collection
    var $primary = false; // primary key, if specified will be used to correctly map submited collection rows on old values
    // $index and  $primary will be used only if this form is part of a collection

    //variables that can be initialized by constructor params
    var $name;					// name for FORM tag, this should also be the name of xml file for this form
    var $dbType = "php";		// framework to be used for running queries, possible values 'php', 'php-pdo', 'joomla'
    var $dbHandle = false;		// database handle will be required for pdo and some other frameworks
    var $template;				// template to be used for generating form
    var $parent = false;
    var $renderer;
    var $loader;
    var $excludeFields;
    var $language = false;
    var $extraCode = '';

    //variables storing form and element details
    var $id;				// id for FORM tag
    var $attributes;		// attributes to output in FORM tag
    var $elements;			// this will be used internally to store form elements and their details

    var $errorString = '';
    var $errorFields = array();

    var $isLoaded = false;
    var $ready = false;

    var $callBack = array(); //this array will hold all the callback functions/closures

    var $inbuiltRenderers = array(
        'table'     => '\X2Form\Renderers\Table\Renderer',
        'div'       => '\X2Form\Renderers\Div\Renderer',
        'bootstrap' => '\X2Form\Renderers\Bootstrap\Renderer',
        'jqueryui'  => '\X2Form\Renderers\Jqueryui\Renderer'
    );



    public function __construct( $name = '', $params  ){
        //$template=false, $lang=false, $dbTyp = 'php', &$dbHnd=false, $idx = false, &$parentForm = false
        $this->name = $name;

        ///initialize variables
        $this->elements = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );
        $this->attributes = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );

        // set params
        foreach( $params as $key => $value ){
            if( in_array( $key, ['index', 'parent', 'template', 'language', 'dbType', 'dbHandle', 'renderer'] ) ){
                $this->$key = $value;
            }elseif( isset( $params['loader']) && is_object( $params['loader']) && is_subclass_of( $params['loader'], 'X2Form\Interfaces\Loader'  )  ){
                $this->loader = $params['loader'];
            }elseif( isset( $params['exclude'] ) ){
                if( is_array( $params['exclude'] ) ){
                    $this->excludeFields = $params['exclude'];
                }elseif( is_string( $params['exclude'] ) ){
                    $this->excludeFields = [ $params['exclude'] ];
                }
                //else ignore exclude
            }elseif( $key != 'from' &&  $key != 'elements' ){
                //everything else except 'from' will be placed in attributes
                $this->attributes[ $key ] = $value;
            }
        }

        if( !$this->renderer ){
            //render using table renderer by default
            $this->renderer = new Renderers\Table\Renderer();
        }

        if( !$this->loader ){
            //load using Autoloader by default
            $this->loader = new Loaders\Auto();
        }

        if( isset( $params['elements']) ){
            //load elements passed as array in the params
            $this->load( $params['elements'], [] );
        }

        if( isset( $params['from']) ){
            $exclude = [];
            if( isset( $params['exclude'] ) ){
                $exclude = $params['exclude'];
            }
            $this->load( $params['from'], $exclude );
        }else{
            //form will be loaded manually
            $this->isLoaded = true;
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
        return array(
            'index',
            'primary',
            'name',
            'dbType',
            'template',
            'renderer',
            'loader',
            'excludeFields',
            'language',
            'extraCode',
            'id',
            'attributes',
            'elements',
            'errorString',
            'errorFields',
            'isLoaded',
            'callBack',
            'ready',
            'inbuiltRenderers'
        );
    }

    public function __call( $name, $arguments ){
        if( preg_match( '/add([a-z]+)/i', $name, $matches ) ){
            //add element of type $name
            $params = [];
            if( is_array( $arguments ) && isset( $arguments[0] ) ){
                $params = $arguments[0];
            }
            $params['type'] = strtolower( $matches[1] );
            return $this->addElement( $params );
        }
    }

    public function addElement( $params ){

        if( isset( $params['type'] ) && $params['type'] && isset( $params['name'] ) && !isset( $this->elements[ $params['name'] ] ) ){
            $type = strtolower( $params['type'] );
            $params['parent'] = $this;
            $params['dbType'] = &$this->dbType;
            $params['language'] = &$this->language;
            $params['dbHandle'] = &$this->dbHandle;

            if( $type == 'collection' ){
                $this->elements[$params['name']] = new Collection( $params );
            }elseif( $type == 'group' ){
                $this->elements[$params['name']] = new Group( $params );
            }else{
                $this->elements[$params['name']] = new Element( $params );
            }
        }
        return false;
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
        foreach( $obj->elements as &$element ){
            $element->parent = &$obj;
            $element->dbHandle = &$dbh;
        }
        return $obj;
    }


    /*****************************************************************************
     * function finalize()
     *      evaluates all queries, php expressions passed for finding datasets for
     *      elements like dropdowns etc. Also computes final output names for fields
     *      It is essential to call this function before rendering or validation
     *****************************************************************************/
    function finalize(){
        if( !$this->ready ){
            foreach( $this->elements as &$element ){
                //set $this as parent of all elements
                //we do this here because we don't want to call function for adding elements
                //nor do we want the user to have pain of specifying the parent when its so obvious
                $element->parent = $this;
                //now finalize the elment itself
                $element->finalize();
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
     * function renderRaw()
     * 		Render a raw HTML template without adding any values and without template file
     * returns:
     * 		the rendered HTML as string.
     * 		Note that it does'nt send output to screen
     ****************************************************************************/
    function renderRaw(){
        if( !$this->isLoaded ){
            return $this->errorString;
        }
        $this->finalize();

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
        if( $this->validate( $postedData, $oldData ) ){
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
    public function validate( $postedData=null, $oldData = null ){
        $this->errorString = '';

        if( is_array( $postedData ) ){
            $this->clear(); // clear initial data, this is required specially for collection
            $this->setValues( $postedData );
        }
        if( is_array( $oldData ) ){
            $this->storeOldValues( $oldData );
        }

        foreach( $this->elements as &$element ){
            //skip submit buttons and files
            //we will handle files in separate call

            if( $element->type == 'captcha' ){
                if( !$element->provider->validate( $postedData ) ){
                    $element->errorString = $element->provider->error;
                    $this->errorFields[ $element->name ] = $element->provider->error;
                }
            }elseif( $element->type != 'submit' && $element->type != 'button' && $element->type != 'reset' && $element->type != 'label' ){

                $val = $element->validate();
                if( strlen( $val ) > 0 ){
                    $this->errorFields[ $element->name ] = $val;
                    $this->errorString .= $this->errorFields[ $element->name ];
                }
            }
        }

        $this->errorString = implode( '<br/>', $this->errorFields );
        if( $this->errorString ){
            //clear the filenames
            foreach( $this->elements as &$element ){
                if( $element->type == 'file' ){
                    if( $element->oldValue ){
                        $element->value = $this->oldValue ;
                    }else{
                        $element->value = "" ;
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
        foreach( $this->elements as &$element ){
            if( $element instanceOf \X2Form\Group ){
                //pass all values to group let it take what it has
                $element->setValues( $formValues );
            }elseif( isset($formValues[$element->name] ) ){
                if( $element instanceOf \X2Form\Collection ){
                    $element->setValues( $formValues[ $element->name ] );
                }elseif( $element->type != 'submit' && $element->type != 'button' ){
                    $element->value = $formValues[ $element->name ];
                }
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
        foreach( $this->elements as &$element ){
            //skip submit buttons and files
            if( $element instanceOf \X2Form\Collection ){
                $values[ $element->name ] = $element->getValues();
            }elseif( !in_array( $element->type, array( 'submit', 'button', 'reset', 'image' ) ) ){
                $values[ $element->name ] = $element->value;
            }elseif( $element instanceof \X2Form\Group ){
                $gValues = $element->getValues();
                foreach( $gValues as $name => $gValue ){
                    $values[ $name ] = $gValue;
                }
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
        foreach( $this->elements as &$element ){
            if( $element instanceOf \X2Form\Group ){
                //pass all values to group let it take what it has
                $element->storeOldValues( $oldValues );
            }elseif( isset($oldValues[$element->name] ) ){
                if( $element instanceOf \X2Form\Collection ){
                    $element->storeOldValues( $oldValues[ $element->name ] );
                }elseif( $element->type != 'submit' && $element->type != 'button' ){
                    $element->oldValue = $oldValues[ $element->name ];
                }
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
        foreach( $this->elements as &$element ){
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
        foreach( $this->elements as &$element ){
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
     * function clear()
     * 		This function clears all the values set in the form elements except buttons and labels.
     ***********************************************************************************/
    public function clear(){
        $this->errorString = '';
        foreach( $this->elements as &$element ){
            if( $element->type != 'submit' && $element->type != 'button' && $element->type != 'label' && $element->type != 'reset' ) {
                $element->clear();
            }
        }
        return true;
    }

}