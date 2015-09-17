<?php
/**
 * Abstract Class BasicRenderer
 * Provides basic common functions(make functions)
 * to Renderer, GroupRenderer, CollectionRenderer and ElementRenderer
 * This class will not be called directly,
 * and
 */

namespace X2Form\Renderers\Bootstrap;


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
            $id = $element->parent->name."_".$element->outputName;
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

            $toolTipText = $element->errorString;;
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
            return '<label for="'.$this->makeId($element).'" class="control-label">'.$element->label().'</label>';
        }
        $element->provider->data['description'] = $element->label();

        //for captcha we include the challenge below the label
        $theme = new MulticaptchaTheme();
        return '<label for="'.$this->makeId($element).'" class="control-label">'.$theme->renderLabel( $element->provider->data ).'</label>';
    }

    public function makeDescription( &$element ){
        $desc = $element->description();
        if( trim( strlen( $desc ) ) > 0 ){
            return '<p class="help-block">'.$desc.'</p>';
        }
        return '';
    }

    public function addClass( $className, $attributes ){
        if( !isset( $attributes['class']) ){
            $attributes['class'] = $className;
        }else{
            $classes= explode( ' ', preg_replace('/(\s\t\n)+/',' ', $attributes['class']) );
            if( !in_array( $className, $classes )){
                $classes[] = $className;
                $attributes['class'] = implode( ' ', $classes);
            }
        }
        return $attributes;
    }

    public function removeClass( $className, $attributes ){
        if( isset( $attributes['class']) ){
            return  preg_replace( ['/^'.$className.'\s/', '/\s'.$className.'\s/', '/\s'.$className.'$/', '/^'.$className.'$/' ], ' ', $attributes['class'] );
        }
        return $attributes;
    }
} 