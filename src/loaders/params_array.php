<?php
namespace X2Form\Loaders;
use X2Form\Helpers;
use ArrayObject;

class ParamsArray{


    /*****************************************************************************
     * function load()
     * 		Loads form elements from params array, generally through default constructor
     * parameters:
     * 		$elementArray - an array of element and their params
     *      $form - Form object in which the elements are to be loaded
     * returns:
     * 		true - if the elements are loaded successfully
     * 		string - Error message
     ****************************************************************************/
    public static function load( &$form, $elementArray ){
        if( is_array( $elementArray ) ){
            foreach( $elementArray as $elem ){
                //only elements with type and name will be loaded
                if( isset( $elem['type'] ) && isset( $elem['name'] ) ){
                    $form->addElement( $elem );
                }
            }
        }

        $form->isLoaded = true;
        return Logg( 'Success', '', 'Elements array is loaded successfully.');
    }

};

?>