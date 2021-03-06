<?php
namespace X2Form\Renderers\Bootstrap;

class ElementRenderer extends BasicRenderer{

    function __construct(){
        $this->collectionRenderer = new CollectionRenderer();
        $this->groupRenderer = new GroupRenderer();
        $this->stepRenderer = new StepRenderer();

        $this->collectionRenderer->elementRenderer = &$this;
        $this->groupRenderer->elementRenderer = &$this;
        $this->stepRenderer->elementRenderer = &$this;
    }

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
        $attributes = $this->addClass( 'btn', $element->attributes );
        $attributes = $this->addClass( 'btn-default', $element->attributes );
        $attribTxt = $this->makeAttributes( $attributes );
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
        $attributes = $this->addClass( 'form-control', $element->attributes );
        $attribTxt = $this->makeAttributes( $attributes );
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

    public function renderPassword( &$element ){
        return $this->renderText( $element );
    }

    public function renderTextarea( &$element ){
        $id = $this->makeId( $element );
        $toolTip = $this->makeTooltip( $element );
        //add the character count script if character count is enabled
        $appendText = '';
        if( isset( $element->attributes['maxlength'] )
            && is_numeric( $element->attributes['maxlength'] )
            && isset($element->config['showcharcount'] )
            && $element->config['showcharcount'] == true
        ){
            $onChangeScript = "document.getElementById('".$id.'-char-count'."').innerHTML = Math.max(0,(parseInt(this.getAttribute('maxlength'))-this.value.length)).toString()+' characters remaining.'";
            $appendText = '<span id="'.$id.'-char-count" class="char-count" >'
                .($element->attributes['maxlength'] - strlen( $element->value ) )
                .' characters remaining</span>';
            if( isset( $element->attributes['onkeyup'] ) ){
                $element->attributes['onkeyup'] = $onChangeScript .$element->attributes['onchange'];
            }else{
                $element->attributes['onkeyup'] = $onChangeScript ;
            }

        }
        $attributes = $this->addClass( 'form-control', $element->attributes );
        $attribTxt = $this->makeAttributes( $attributes );
        $eventsTxt = $this->makeEvents( $element->events );
        return "<textarea id=\"".$id."\" name=\"$element->outputName\" $toolTip $attribTxt $eventsTxt >{$element->value}</textarea>".$appendText;

    }

    public function renderRadio( &$element ){
        $id = $this->makeId( $element );
        $toolTip = $this->makeTooltip( $element );
        $attribTxt = $this->makeAttributes( $element->attributes );
        $eventsTxt = $this->makeEvents( $element->events );

        if( isset( $element->config['direction'] ) && $element->config['direction'] == 'vertical' ){
            $openWrapper = '<div class="radio"><label>';
            $closeWrapper = '</label></div>';
        }else{
            $openWrapper = ' <label class="radio-inline">';
            $closeWrapper = '</label>';
        }

        if( count( $element->data ) == 0 ){
            $str = $openWrapper
                ."<input type=\"radio\" id=\"".$id."\" name=\"$element->outputName\" value=\"$element->value\" $attribTxt $eventsTxt />"
                .$closeWrapper;
        }else{
            $cnt = 0;
            $str = '';
            foreach( $element->data as $opt ){
                //var_dump( $opt );
                $checked='';
                if( $opt['value'] == $element->value ){ $checked = 'checked="checked"'; }

                $str .= $openWrapper
                    ."<input type=\"radio\" id=\"$id$cnt\" name=\"".$element->outputName."\" value=\"{$opt['value']}\" $checked $attribTxt $eventsTxt />{$opt['label']}"
                    .$closeWrapper;
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
            $openWrapper = '<div class="checkbox"><label>';
            $closeWrapper = '</label></div>';
        }else{
            $openWrapper = ' <label class="checkbox-inline">';
            $closeWrapper = '</label>';
        }

        if( count( $element->data ) == 0 ){
            $str = $openWrapper
                ."<input type=\"checkbox\" id=\"".$id."\" name=\"$element->outputName\" value=\"$element->value\" $attribTxt $eventsTxt />"
                .$closeWrapper;
        }elseif( count( $element->data ) == 1 ){
            $opt = $element->data[0];
            $checked = '';
            if( is_array( $element->value ) && in_array( $opt['value'], $element->value ) ){
                $checked = 'checked="checked"';
            }elseif( $opt['value'] == $element->value ){
                $checked = 'checked="checked"';
            }
            $str = $openWrapper
                ."<input type=\"checkbox\" id=\"".$id."\" name=\"".$element->outputName."\" value=\"{$opt['value']}\" $checked $attribTxt $eventsTxt />{$opt['label']}"
                .$closeWrapper;

        }else{
            $cnt = 0;
            $str = '';
            foreach( $element->data as $opt ){
                $checked = '';
                if( is_array( $element->value ) && in_array( $opt['value'], $element->value ) ){
                    $checked = 'checked="checked"';
                }elseif( $opt['value'] == $element->value ){
                    $checked = 'checked="checked"';
                }
                $str .= $openWrapper
                    ."<input type=\"checkbox\" id=\"".$id."$cnt\" name=\"".$element->outputName."[$cnt]\" value=\"{$opt['value']}\" $checked $attribTxt $eventsTxt />{$opt['label']}"
                    .$closeWrapper;
                $cnt++;
            }
        }
        return $str;
    }

    public function renderDropdown( &$element ){
        $id = $this->makeId( $element );
        $toolTip = $this->makeTooltip( $element );
        $attributes = $this->addClass( 'form-control', $element->attributes );
        $attribTxt = $this->makeAttributes( $attributes );
        $eventsTxt = $this->makeEvents( $element->events );

        $multipleSuffix = '';
        if( isset( $element->attributes['multiple'] ) ){
            $multipleSuffix = "[]";
        }

        $str = "<select id=\"".$id."\" name=\"".$element->outputName.$multipleSuffix."\" $toolTip $attribTxt $eventsTxt >";
        if( $promt = $element->prompt() ){
            $str .= "<option value=\"\" >{$promt}</option>";
        }
        if( is_array( $element->data ) ){
            foreach( $element->data as $opt ){
                if( is_array( $opt ) ){
                    $option = $opt['label'];
                    $val = $opt['value'];
                }else{
                    $option = $opt;
                    $val = $opt;
                }
                if( isset( $element->attributes['multiple'] ) && is_array( $element->value ) && in_array( $val, $element->value ) ){
                    $selected = 'selected="selected"';
                }elseif( $val == $element->value ){
                    $selected = 'selected="true"';
                }else{
                    $selected = '';
                }
                $str .= "<option value=\"$val\" $selected >$option</option>";
            }
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
        if( isset( $element->config['language'] ) ){
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

    public function renderCaptcha( &$element ){
        $toolTip = $this->makeTooltip( $element );
        $element->provider->data['tooltip'] = $toolTip;
        $attributes = $this->addClass( 'form-control', $element->attributes );
        //$attribTxt = $this->makeAttributes( $element->attributes );
        //$eventsTxt = $this->makeEvents( $element->events );
        $theme = new MulticaptchaTheme();
        if( isset( $element->attributes['class'] ) ){
            $theme->fieldClass = $element->attributes['class'];
        }
        return $theme->render( $element->provider->data );
    }

    public function refreshCaptcha( &$element ){
        $toolTip = $this->makeTooltip( $element );
        $element->provider->data['tooltip'] = $toolTip;
        $attributes = $this->addClass( 'form-control', $element->attributes );
        //$attribTxt = $this->makeAttributes( $element->attributes );
        //$eventsTxt = $this->makeEvents( $element->events );
        $theme = new MulticaptchaTheme();
        if( isset( $attributes['class'] ) ){
            $theme->fieldClass = $element->attributes['class'];
        }
        return $theme->refresh( $element->provider->data );
    }

    public function renderChildren( &$elements, $direction='vertical', $wrap=true ){
        $html = '';
        $hiddenElems = '';

        if( $direction == 'inline' ){
            //horizontal alignment using blank space as seperator
            $html .= '<div class="form-group form-inline">';
            foreach( $elements as $i => &$element ){
                if( $element instanceof \X2Form\Collection ){
                    $html .= $this->makeLabel( $element ).' '
                        .$this->collectionRenderer->render( $element )
                        .$this->makeDescription( $element ).' ';
                }elseif( $element instanceof \X2Form\Group ){
                    $html .=  $this->makeLabel( $element ).' '
                        .$this->groupRenderer->render( $element ).' '
                        .$this->makeDescription( $element ).' ';
                }elseif( $element instanceof \X2Form\Step ){
                    if( $element->isActive ){
                        $html .= $this->stepRenderer->render( $element );
                    }
                }elseif( $element->type == 'hidden' ){
                    $hiddenElems .= $this->render( $element );
                }elseif( $element->type == 'label' ){
                    $html .= $this->render( $element ).' ';
                }elseif( in_array( $element->type, ['submit', 'reset', 'password', 'image'] ) ){
                    $html .= $this->render( $element ).' ';
                }else{
                    $html .= $this->makeLabel( $element ).' '
                        .$this->render( $element ).' '
                        .$this->makeDescription( $element ).' ';
                }
            }
            $html .= '</div> ';

        }elseif( $direction == 'horizontal' ){
            //horizontal alignment using blank space as seperator
            $html .= '<div class="form-group form-inline">';
            foreach( $elements as $i => &$element ){
                if( $element instanceof \X2Form\Collection ){
                    $html .= '<div class="form-group">'
                        .$this->makeLabel( $element )
                        .$this->collectionRenderer->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }elseif( $element instanceof \X2Form\Group ){
                    $html .=  '<div class="form-group">'
                        .$this->makeLabel( $element )
                        .$this->groupRenderer->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }elseif( $element instanceof \X2Form\Step ){
                    if( $element->isActive ){
                        $html .=  '<div class="form-group">'
                            .$this->stepRenderer->render( $element )
                            .'</div> ';
                    }
                }elseif( $element->type == 'hidden' ){
                    $hiddenElems .= $this->render( $element );
                }elseif( $element->type == 'label' ){
                    $html .= '<div class="form-group">'
                        .$this->render( $element )
                        .'</div> ';
                }else{
                    $html .= '<div class="form-group">'
                        .$this->makeLabel( $element ).' '
                        .$this->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }
            }
            $html .= '</div> ';

        }else{
            // vertical alignment using tables
            $html .= '<div class="form-group">';
            foreach( $elements as $i => &$element ){
                if( $element instanceof \X2Form\Collection ){
                    $html .= '<div class="form-group">'
                        .$this->makeLabel( $element )
                        .$this->collectionRenderer->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }elseif( $element instanceof \X2Form\Group ){
                    $html .=  '<div class="form-group">'
                        .$this->makeLabel( $element )
                        .$this->groupRenderer->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }elseif( $element instanceof \X2Form\Step ){
                    if( $element->isActive ){
                        $html .=  '<div class="form-group">'
                            .$this->stepRenderer->render( $element )
                            .'</div> ';
                    }
                }elseif( $element->type == 'hidden' ){
                    $hiddenElems .= $this->render( $element );
                }elseif( $element->type == 'label' ){
                    $html .= '<div class="form-group">'
                        .$this->render( $element )
                        .'</div> ';
                }else{
                    $html .= '<div class="form-group">'
                        .$this->makeLabel( $element ).' '
                        .$this->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }
            }
            $html .= '</div> ';
        }
        return $html.$hiddenElems;
    }


} 