<?php
namespace X2Form\Renderers\Table;

class ElementRenderer extends BasicRenderer {

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

    public function renderPassword( &$element ){
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

        if( count( $element->data ) == 0 ){
            $str = "<input type=\"radio\" id=\"".$id."\" name=\"$element->outputName\" value=\"$element->value\" $attribTxt $eventsTxt />";
        }else{
            $cnt = 0;
            $str = '';
            foreach( $element->data as $opt ){
                //var_dump( $opt );
                $checked='';
                if( $opt['value'] == $element->value ){ $checked = 'checked="checked"'; }

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

        if( count( $element->data ) == 0 ){
            $str = "<input type=\"checkbox\" id=\"".$id."\" name=\"$element->outputName\" value=\"$element->value\" $attribTxt $eventsTxt />";
        }elseif( count( $element->data ) == 1 ){
            $opt = $element->data[0];
            $checked = '';
            if( is_array( $element->value ) && in_array( $opt['value'], $element->value ) ){
                $checked = 'checked="checked"';
            }elseif( $opt['value'] == $element->value ){
                $checked = 'checked="checked"';
            }
            $str = "<input type=\"checkbox\" id=\"".$id."\" name=\"".$element->outputName."\" value=\"{$opt['value']}\" $checked $attribTxt $eventsTxt /><label for=\"$id\">{$opt['label']}</label>".$spacer;

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
                $selected = 'selected="selected"';
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

    public function renderGroup( &$group ){
        //generate normal html
        //generate normal html
        $html = '<table cellpadding="0" cellspacing="0" border="0">';
        $cnt=1;
        $hiddenElems = '';

        foreach( $group->elements as $i => $element ){
            if( $cnt%2 == 0){ $class = 'even'; }else{ $class= 'odd'; }

            if( $element->type == 'hidden' ){
                $hiddenElems .= " [{$element->name}]";
            }elseif( $element->type == 'label' ){
                $html .= '<tr class="'.$class.'"><td valign="top" colspan="2">'
                    ."[{$element->name}]".' &nbsp;</td></tr>';
            }else{
                $cnt++;
                $html .= '<tr class="'.$class.'"><td valign="top">'
                    ."[{$element->name}_label]".'</td><td>'
                    ."[{$element->name}]".' &nbsp; <i>'
                    ."[{$element->name}_description]".'</i></td></tr>';
            }
        }

        $html .= '</table>'.$hiddenElems;

        return $html;

    }


    public function renderCaptcha( &$element ){
        $toolTip = $this->makeTooltip( $element );
        $element->provider->data['tooltip'] = $toolTip;
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
        //$attribTxt = $this->makeAttributes( $element->attributes );
        //$eventsTxt = $this->makeEvents( $element->events );
        $theme = new MulticaptchaTheme();
        if( isset( $element->attributes['class'] ) ){
            $theme->fieldClass = $element->attributes['class'];
        }
        return $theme->refresh( $element->provider->data );
    }

    public function renderChildren( &$elements, $direction='vertical', $wrap=true ){
        $html = '';
        $hiddenElems = '';

        if( $direction == 'inline' ){
            //horizontal alignment using blank space as seperator
            foreach( $elements as $i => &$element ){
                if( $element instanceof \X2Form\Collection ){
                    $html .= $this->makeLabel( $element ).' '
                        .$this->collectionRenderer->render( $element ).' '
                        .$this->makeDescription( $element ).' &nbsp; ';
                }elseif( $element instanceof \X2Form\Group ){
                    $html .= $this->makeLabel( $element ).' '
                        .$this->groupRenderer->render( $element ).' '
                        .$this->makeDescription( $element ).' &nbsp; ';
                }elseif( $element instanceof \X2Form\Step ){
                    if(  $element->isActive ){
                        $html .= $this->stepRenderer->render( $element );
                    }
                }elseif( $element->type == 'hidden' ){
                    $hiddenElems .= $this->render( $element );
                }elseif( $element->type == 'label' ){
                    $html .= $this->render( $element ).' &nbsp; ';
                }else{
                    $html .= $this->makeLabel( $element ).' '
                        .$this->render( $element ).'  '
                        .$this->makeDescription( $element ).' &nbsp; ';
                }
            }

        }elseif( $direction == 'horizontal' ){
            //horizontal alignment using tables
            //generate normal html
            if( $wrap ){
                $html = '<table cellpadding="0" cellspacing="0" border="0"><tr>';
            }

            foreach( $elements as $i => &$element ){
                if( $element instanceof \X2Form\Collection ){
                    $html .= '<td valign="top">'
                        .$this->makeLabel( $element ).'</td><td>'
                        .$this->collectionRenderer->render( $element ).' &nbsp; '
                        .$this->makeDescription( $element )
                        .'</td>';
                }elseif( $element instanceof \X2Form\Group ){
                    $html .= '<td valign="top">'
                        .$this->makeLabel( $element ).'</td><td>'
                        .$this->groupRenderer->render( $element ).' &nbsp; '
                        .$this->makeDescription( $element )
                        .'</td>';
                }elseif( $element instanceof \X2Form\Step ){
                    if(  $element->isActive ){
                        $html .= '<td valign="top" colspan="2">'
                            .$this->stepRenderer->render( $element ).' &nbsp; '
                            .'</td>';
                    }
                }elseif( $element->type == 'hidden' ){
                    $hiddenElems .= $this->render( $element );
                }elseif( $element->type == 'label' ){
                    $html .= '<td valign="top" colspan="2">'
                        .$this->render( $element ).' &nbsp;</td>';
                }else{
                    $html .= '<td valign="top">'
                        .$this->makeLabel( $element ).'</td><td>'
                        .$this->render( $element ).' &nbsp; '
                        .$this->makeDescription( $element )
                        .'</td>';
                }
            }
            $html .= '</tr>';
            if( $wrap){
                $html .= '</table>';
            }

        }else{
            // vertical alignment using tables
            //generate normal html
            if( $wrap){
                $html = '<table cellpadding="0" cellspacing="0" border="0">';
            }
            $cnt=1;

            foreach( $elements as $i => &$element ){
                if( $cnt%2 == 0){ $class = 'even'; }else{ $class= 'odd'; }

                if( $element instanceof \X2Form\Collection ){
                    $cnt++;
                    $html .= '<tr class="'.$class.'"><td valign="top">'
                        .$this->makeLabel( $element ).'</td><td>'
                        .$this->collectionRenderer->render( $element ).' &nbsp; '
                        .$this->makeDescription( $element ).'</td></tr>';
                }elseif( $element instanceof \X2Form\Group ){
                    $cnt++;
                    $html .= '<tr class="'.$class.'"><td valign="top">'
                        .$this->makeLabel( $element ).'</td><td>'
                        .$this->groupRenderer->render( $element ).' &nbsp; '
                        .$this->makeDescription( $element ).'</td></tr>';
                }elseif( $element instanceof \X2Form\Step ){
                    if(  $element->isActive ){
                        $cnt++;
                        $html .= '<tr class="'.$class.'"><td valign="top" colspan="2">'
                            .$this->stepRenderer->render( $element ).'</td></tr>';
                    }
                }elseif( $element->type == 'hidden' ){
                    $hiddenElems .= $this->render( $element );
                }elseif( $element->type == 'label' ){
                    $html .= '<tr class="'.$class.'"><td valign="top" colspan="2">'
                        .$this->render( $element ).' &nbsp;</td></tr>';
                }else{
                    $cnt++;
                    $html .= '<tr class="'.$class.'"><td valign="top">'
                        .$this->makeLabel( $element ).'</td><td>'
                        .$this->render( $element ).' &nbsp; '
                        .$this->makeDescription( $element ).'</td></tr>';
                }
            }
            if( $wrap){
                $html .= '</table>';
            }
        }
        return $html.$hiddenElems;
    }

} 