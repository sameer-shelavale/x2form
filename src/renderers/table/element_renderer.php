<?php
namespace X2Form\Renderers\Table;

class ElementRenderer {

    /***********************************************************************************
     * function render()
     * 		This function renders form element and returns the output as string.
     * 		please note that It does not display it to screen.
     ***********************************************************************************/
    public function render( &$element ){

        $element->finalize();

        $functionName = 'render'.ucfirst( $element->type );
        if( method_exists( $this, $functionName ) ){
            return $this->$functionName( $element );
        }else{
            return "The Renderer does not support element type {$element->type}";
        }
    }

    public function renderButton( &$element ){
        $id = $this->makeId( $element );
        $toolTip = $this->makeTooltip( $element );
        $attribTxt = $this->makeAttributes( $element->attributes );
        $eventsTxt = $this->makeEvents( $element->events );

        //the value here is actually label displayed on buttons
        //sand it can be different in different languages
        $btnValue = '';
        if( isset( $element->config['language'] ) ){
            $btnValue = $element->hasLanguage( $element->config->language, 'value' );
        }

        if( !$btnValue && isset( $element->value ) ){
            $btnValue = $element->value;
        }
        $str = "<input id=\"".$id."\" type=\"$element->type\" name=\"{$element->outputName}\" value=\"{$btnValue}\" $toolTip $attribTxt $eventsTxt />";
        return $str;
    }

    public function renderSubmit( &$element ){
        return $this->renderButton( $element );
    }

    public function renderReset( &$element ){
        return $this->renderButton( $element );
    }

    public function renderText( &$element ){
        $id = $this->makeId( $element );
        $toolTip = $this->makeTooltip( $element );
        $attribTxt = $this->makeAttributes( $element->attributes );
        $eventsTxt = $this->makeEvents( $element->events );

        $str = "<input id=\"".$id."\" type=\"$element->type\" name=\"{$element->outputName}\" value=\"{$element->value}\" $toolTip $attribTxt $eventsTxt />";
        return $str;
    }

    public function renderHidden( &$element ){
        return $this->renderText( $element );
    }

    public function renderImage( &$element ){
        return $this->renderText( $element );
    }

    public function renderPassowrd( &$element ){
        return $this->renderText( $element );
    }

    public function renderTextarea( &$element ){
        $id = $this->makeId( $element );
        $toolTip = $this->makeTooltip( $element );
        $attribTxt = $this->makeAttributes( $element->attributes );
        $eventsTxt = $this->makeEvents( $element->events );
        return "<textarea id=\"".$id."\" name=\"$element->outputName\" $toolTip $attribTxt $eventsTxt >{$element->value}</textarea>";
    }

    public function renderRadio( &$element ){
        $id = $this->makeId( $element );
        $toolTip = $this->makeTooltip( $element );
        $attribTxt = $this->makeAttributes( $element->attributes );
        $eventsTxt = $this->makeEvents( $element->events );

        if( isset( $element->config['direction'] ) && $element->config['direction'] == 'vertical' ){
            $spacer='<br/>';
        }else{
            $spacer=' ';
        }

        if( count( $element->optionData ) == 0 ){
            $str = "<input type=\"radio\" id=\"".$id."\" name=\"$element->outputName\" value=\"$element->value\" $attribTxt $eventsTxt />";
        }else{
            $cnt = 0;
            $str = '';
            foreach( $element->optionData as $opt ){
                //var_dump( $opt );
                $checked='';
                if( $opt['value'] == $element->value ){ $checked = 'checked="true"'; }

                $str .= "<input type=\"radio\" id=\"$id$cnt\" name=\"".$element->outputName."\" value=\"{$opt['value']}\" $checked $attribTxt $eventsTxt /><label for=\"$id$cnt\">{$opt['label']}</label>".$spacer;
                $cnt++;
            }
        }
        return $str;
    }

    public function renderCheckbox( &$element ){
        $id = $this->makeId( $element );
        $toolTip = $this->makeTooltip( $element );
        $attribTxt = $this->makeAttributes( $element->attributes );
        $eventsTxt = $this->makeEvents( $element->events );

        if( isset( $element->config['direction'] ) && $element->config['direction'] == 'vertical' ){
            $spacer='<br/>';
        }else{
            $spacer=' ';
        }

        if( count( $element->optionData ) == 0 ){
            $str = "<input type=\"checkbox\" id=\"".$id."\" name=\"$element->outputName\" value=\"$element->value\" $attribTxt $eventsTxt />";
        }elseif( count( $element->optionData ) == 1 ){
            $opt = $element->optionData[0];
            $checked = '';
            if( is_array( $element->value ) && in_array( $opt['value'], $element->value ) ){
                $checked = 'checked="true"';
            }elseif( $opt['value'] == $element->value ){
                $checked = 'checked="true"';
            }
            $str = "<input type=\"checkbox\" id=\"".$id."\" name=\"".$element->outputName."\" value=\"{$opt['value']}\" $checked $attribTxt $eventsTxt /><label for=\"$id\">{$opt['label']}</label>".$spacer;

        }else{
            $cnt = 0;
            $str = '';
            foreach( $element->optionData as $opt ){
                $checked = '';
                if( is_array( $element->value ) && in_array( $opt['value'], $element->value ) ){
                    $checked = 'checked="true"';
                }elseif( $opt['value'] == $element->value ){
                    $checked = 'checked="true"';
                }
                $str .= "<input type=\"checkbox\" id=\"".$id."$cnt\" name=\"".$element->outputName."[$cnt]\" value=\"{$opt['value']}\" $checked $attribTxt $eventsTxt /><label for=\"$id$cnt\">{$opt['label']}</label>".$spacer;
                $cnt++;
            }
        }
        return $str;
    }

    public function renderDropdown( &$element ){
        $id = $this->makeId( $element );
        $toolTip = $this->makeTooltip( $element );
        $attribTxt = $this->makeAttributes( $element->attributes );
        $eventsTxt = $this->makeEvents( $element->events );

        $multipleSuffix = '';
        if( isset( $element->attributes['multiple'] ) ){
            $multipleSuffix = "[]";
        }

        $str = "<select id=\"".$id."\" name=\"".$element->outputName.$multipleSuffix."\" $toolTip $attribTxt $eventsTxt >";
        if( $promt = $element->prompt() ){
            $str .= "<option value=\"\" >{$promt}</option>";
        }
        foreach( $element->optionData as $opt ){
            if( is_array( $opt ) ){
                $option = $opt['label'];
                $val = $opt['value'];
            }else{
                $option = $opt;
                $val = $opt;
            }
            if( isset( $element->attributes['multiple'] ) && is_array( $element->value ) && in_array( $val, $element->value ) ){
                $selected = 'selected="true"';
            }elseif( $val == $element->value ){
                $selected = 'selected="true"';
            }else{
                $selected = '';
            }
            $str .= "<option value=\"$val\" $selected >$option</option>";
        }
        $str .= "</select>";

        return $str;
    }

    public function renderFile( &$element ){
        $id = $this->makeId( $element );
        $toolTip = $this->makeTooltip( $element );
        $attribTxt = $this->makeAttributes( $element->attributes );
        $eventsTxt = $this->makeEvents( $element->events );

        $str = "<input id=\"".$id."\" type=\"$element->type\" name=\"{$element->outputName}\" $toolTip $attribTxt $eventsTxt />";
        if( $element->value ){
            $str .= " <i>( ".$element->config['uploaddirectory'].$element->value." )</i>";
        }
        return $str;
    }

    public function renderLabel( &$element ){
        $id = $this->makeId( $element );
        $toolTip = $this->makeTooltip( $element );
        $attribTxt = $this->makeAttributes( $element->attributes );
        $eventsTxt = $this->makeEvents( $element->events );

        $labelVal = '';
        if( array_key_exists( 'language', $element->config )){
            $labelVal = $element->hasLanguage( $element->config->language, 'value' );
        }
        if( !$labelVal && isset( $element->value ) ){
            $labelVal = $element->value;
        }
        $str = '';
        if( $labelVal ){
            $str = "<label id=\"".$id."\" name=\"{$element->outputName}\"  $attribTxt $eventsTxt />{$labelVal}</label>";
        }
        return $str;
    }


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


} 