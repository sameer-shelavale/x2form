<?php
namespace X2Form;
require_once ( rtrim( __DIR__, '/'). '/helpers/dbhelper.php' );
require_once ( rtrim( __DIR__, '/'). '/helpers/logger.php' );

//Auto load the required php classes
spl_autoload_register( function($className){
    $curDir = rtrim( __DIR__, '/');
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