<?php
/*******************************************************************************************************
 * class Logger
 * Logger class is used for logging errors to screen or text file, 
 * It is useful in webservices and APIs to return structured results.
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
 * Dependencies : class.dbhelper.php, class.logg.php
 *******************************************************************************************************/
namespace X2Form\Loaders;
use X2Form\Helpers\Logger;

class Simplexml{


    /*****************************************************************************
     * function load()
     * 		Loads form information from a object of type SimpleXMLElement
     * parameters:
     * 		$formXmlObj - a SimpleXMLElement object
     *      $form - Form object in which the xml elements are to be loaded
     * returns:
     * 		true - if the SimpleXmlElement is loaded successfully
     * 		string - Error message
     ****************************************************************************/
	public static function load( &$form, $formXmlObj ){

        //attributes for the FORM tag, that are to be read from xml, if provided
        $fAttr =  array( 'method'=>'', 'action'=>'', 'enctype'=>'', 'target'=>'_self' );

        //find form attributes
        foreach( $formXmlObj->attributes() as $k => $v ){
            if( array_key_exists( $k, $fAttr ) ){
                $fAttr[ strtolower( "$k" ) ] = "$v";
            }
        }
        $form->attributes = $fAttr;


        //find the elements in the form
        $elems = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );


        foreach( $formXmlObj->elements->children() as $elem ){

            //create X2Form\Element object and store it in array
            $tmp = $form->createNode( $elem );

            if( $tmp ){
                $elems[ $tmp->name ] = $tmp;
            }
        }


        //store all the X2Form\Element objects we have created in elements property of this class
        $form->elements = $elems;
        $form->isLoaded = true;
        return Logg( 'Success', '', 'SimpleXml is loaded successfully.');
	}


};

?>