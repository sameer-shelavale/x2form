<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 9/18/15
 * Time: 1:02 AM
 */

namespace X2Form\Renderers\Table;


abstract class BasicRenderer {

    /*
     * render function must be implemented by the child classes
     */
    abstract function render( &$object );

    /*
     * Common functions
     */
    public function makeId( &$element ){
        if( $element->id == '' ){
            if( property_exists( $element, 'outputName' ) ){
                $id = $element->parent->name."_".$element->outputName;
            }else{
                $id = $element->parent->name."_".$element->name;
            }
        }else{
            $id = $element->id;
        }
        return $id;
    }

    public function makeTooltip( &$element ){
        $toolTipText = '';
        if( strlen( $element->errorString ) > 0 ){
            if( isset( $element->attributes['class'] ) ){
                $element->attributes['class'] = "errorfield ".$element->attributes['class'];
            }else{
                $element->attributes['class'] = "errorfield";
            }

            $toolTipText = $element->errorString;
        }

        if( strlen( $element->title() ) >0 ){
            if( strlen( $toolTipText ) >0 ){
                $toolTipText .= '<hr/>';
            }
            $toolTip = ' title="'.$toolTipText.$element->title().'" ';
        }else{
            $toolTip = ' title="'.$toolTipText.'" ';

        }
        return $toolTip;
    }

    public function makeEvents( $events ){
        //render text for events
        $eventsTxt = '';
        foreach( $events as $e => $action ){
            $eventsTxt .= " $e=\"". str_replace( '"', '&quot;', trim( $action) )."\"";
        }
        return $eventsTxt;
    }

    public function makeAttributes( $attributes ){
        //render text for attributes
        $attribTxt = '';
        foreach( $attributes as $atr => $act ){
            $attribTxt .= " $atr=\"$act\"";
        }
        return $attribTxt;
    }

    public function makeLabel( &$element ){
        if( $element->type != 'captcha' ){
            return $element->label();
        }
        $element->provider->data['description'] = $element->label();

        //for captcha we include the challenge below the label
        $theme = new MulticaptchaTheme();
        return $theme->renderLabel( $element->provider->data );
    }

    public function makeDescription( &$element ){
        $desc = $element->description();
        if( trim( strlen( $desc ) ) > 0 ){
            return '<i>'.$desc.'</i>';
        }
        return '';
    }

} 