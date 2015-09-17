<?php
/*******************************************************************************************************
 * class X2Form\Group
 * Group is used by X2Form for grouping of fields.
 * The Group for now is used only for aligning the grouped elements horizontally or vertically in a single row.
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
 * Copyrights (C) 2012-2013 Sameer Shelavale
 *******************************************************************************************************/
namespace X2Form;
use ArrayObject;
use SimpleXMLElement;

class Group{
    // special properties
    var $direction = 'horizontal'; //the direction in which the grouped elements should be rendered,

	//basic properties
	var $id;
	var $name;
	var $value; // a group wont have any value for now, but keeping this for future use
	var $label; // can be used for input-group in bootstrap
	var $description; // when labels are not enough this can be used
    var $type = 'group';

	//other properties to be initialized by constructor
	var $language = false;
    var $dbType = "php";		// framework to be used for running queries, possible values 'php', 'php-pdo', 'joomla'
	var $dbHandle = false;		// database handle will be required for pdo and some other frameworks
    var $parent = false;

    // properties for internal storage & flags
	var $attributes;		// attributes to be used
	var $elements;			// this will be used internally to store individual form objects
    var $ready = false;
	
    // properties related to validation
	var $errorString = '';
	var $errorFields = array();

	
	public function __construct( $params ){
        //initialize storage vars
        $this->elements = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );
        $this->attributes = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );

        foreach( $params as $key => $value ){
            if( in_array( $key, [ 'direction', 'id', 'name', 'value', 'label', 'description', 'parent', 'template', 'language', 'dbType', 'dbHandle' ] ) ){
                $this->$key = $value;
            }elseif( $key == 'elements' && is_array( $value ) ){
                foreach( $value as $elem ){
                    $this->addElement( $elem );
                }
            }else{
                //everything else is attribute and will be displayed as a attribute of the html tag
                $this->attributes[ $key ] = $value;
            }
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
                //we do this here because we dont want to call function for adding elements
                //nor do we want the user to have pain of specifying the parent when its so obvious
                $element->parent = $this;
                //now finalize the element itself
                $element->finalize();
            }

            $this->ready = true;
        }
    }


	
	/***********************************************************************************
	 * function label()
	 * 		This function return label of element.
	 * 		it is used while rendering element.
	 ***********************************************************************************/
	public function label(){
		return $this->label;
	}
	
	/***********************************************************************************
	 * function description()
	 * 		This function return description of element.
	 * 		it is used while rendering element.
	 ***********************************************************************************/
	public function description(){
		return $this->description;
	}
	
	
	/***********************************************************************************
	 * function validate()
	 * 		This function validates the form data within each sub-form of the collection. 
	 * Returns:
	 *		returns true on success, false on failure.
	 *		it sets $errorString variable on failure.
	 ***********************************************************************************/
	public function validate(){
		$this->errorString = '';

		foreach( $this->elements as &$element ){ //it will create a copy of element without the &
            if( !$element->validate() ){
                if( $this->errorString ){
                    $this->errorString .= '<br/>';
                }
                $this->errorString .= $element->errorString;
            }
		}
        if( !$this->errorString ){
            return true;
        }
		return false;
	}
	
	
	/***********************************************************************************
	 * function setErrorFields()
	 * 		This function sets error messages on each field provided in input array
	 * parameters:
	 *		$eFields  - associative array with field names as keys and error messages as values
	 * 
	 ***********************************************************************************/	
	public function setErrorFields( $eFields ){
		foreach( $this->elements as $index => &$e ){
			$e->setErrorFields( $eFields[ $index ] );
		}
	}


    public function addElement( $params ){
        if( $params['type'] && isset( $params['name'] ) && !isset( $this->elements[ $params['name'] ] ) ){
            $type = strtolower( $params['type'] );
            $params['parent'] = $this;
            $params['dbType'] = &$this->dbType;
            $params['language'] = &$this->language;
            $params['dbHandle'] = &$this->dbHandle;

            if( $type == 'collection' ){
                $this->elements[$params['name']] = new Collection( $params['name'], $params );
            }elseif( $type == 'group' ){
                $this->elements[$params['name']] = new Group( $params );
            }else{
                $this->elements[$params['name']] = new Element( $params );
            }
            return true;
        }
        return false;
    }
	
}


?>