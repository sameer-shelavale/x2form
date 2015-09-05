<?php
namespace X2Form\Loaders;
use SimpleXMLElement;
use \X2Form\Helpers\Logger;

class Auto{

    public function load( &$form, $source, $exclude=null ){
        if( $source instanceof SimpleXMLElement ){
            return \X2Form\Loaders\Simplexml::load( $form, $source, $exclude );
        }elseif( is_subclass_of( $source, 'Illuminate\Database\Eloquent\Model' ) ){
            //passed laravel Model object or classname
            return \X2Form\Loaders\Eloquent::load( $form, $source, $exclude );
        }elseif( is_array( $source ) ){
            //array passed
            return \X2Form\Loaders\ParamsArray::load( $form, $source, $exclude );
        }elseif( is_file( $source ) && is_readable( $source ) ){
            //filename passed
            return \X2Form\Loaders\Xml::loadFile( $form, $source, $exclude );
        }elseif( is_string( $source ) && strlen( $source ) > 50 ){
            //xml is passed
            return \X2Form\Loaders\Xml::load($form, $source, $exclude );
        }
        return Logg( 'Failure', 'The type of source object provided is not supportd.' );
    }
};
