<?php
namespace X2Form\Renderers\Bootstrap;

use X2Form\Collection;

class Renderer extends BasicRenderer implements \X2Form\Interfaces\Renderer{

    var $elementRenderer;
    var $collectionRenderer;
    var $groupRenderer;

    function __construct(){
        $this->elementRenderer = new ElementRenderer();
        $this->collectionRenderer = new CollectionRenderer();
        $this->groupRenderer = new GroupRenderer();

        $this->collectionRenderer->elementRenderer = &$this->elementRenderer;
        $this->collectionRenderer->groupRenderer = &$this->groupRenderer;

        $this->groupRenderer->elementRenderer = &$this->elementRenderer;
        $this->groupRenderer->collectionRenderer = &$this->collectionRenderer;
    }

    public function render( &$form, $addFormTag=true ){

        if( $form->template && is_file( $form->template ) ){
            return $this->renderTemplate($form, $addFormTag);
        }

        //generate normal html
        $html = '';
        $cnt=1;
        $hiddenElems = '';

        foreach( $form->elements as $i => &$element ){

            if( $element instanceof \X2Form\Collection ){
                $cnt++;
                $html .= '<div class="form-group">'
                    .$this->makeLabel( $element )
                    .$this->collectionRenderer->render( $element )
                    .$this->makeDescription( $element )
                    .'</div>';

            }elseif( $element instanceof \X2Form\Group ){
                $cnt++;
                $html .= '<div class="form-group">'
                    .$this->makeLabel( $element )
                    .$this->groupRenderer->render( $element )
                    .$this->makeDescription( $element )
                    .'</div>';

            }elseif( $element->type == 'hidden' ){
                $hiddenElems .= $this->elementRenderer->render( $element );
            }elseif( $element->type == 'label' ){
                $html .= '<div class="form-group">'
                    .$this->elementRenderer->render( $element )
                    .'</div>';
            }else{
                $cnt++;
                $html .= '<div class="form-group">'
                    .$this->makeLabel( $element )
                    .$this->elementRenderer->render( $element )
                    .$this->makeDescription( $element )
                .'</div>';
            }
        }

        $attribs = '';
        foreach( $form->attributes as $key=>$atr ){
            $attribs .= " $key=\"$atr\"";
        }
        if( $addFormTag ){
            $template = "<form name=\"{$form->name}\" id=\"{$form->id}\" $attribs >$html $hiddenElems {$form->extraCode} </form>";
        }else{
            $template = "$html $hiddenElems {$form->extraCode}";
        }

        return $template;
    }


    /*****************************************************************************
     * function renderTemplate()
     * 		Render a raw HTML template without adding any values and without template file
     * parameters:
     * 		$addFormTag - true  - <FORM> tag is wrapped around the rendered html
     * 					- false - <FORM> tag is not added.
     * returns:
     * 		the rendered HTML as string.
     * 		Note that it does'nt send output to screen
     ****************************************************************************/
    function renderTemplate( &$form, $addFormTag= true ){
        //fetch the content of template file
        ob_start();
        include( $form->template );
        $templateContent = ob_get_contents();
        ob_end_clean();
        //$templateContent = file_get_contents( $this->template );
        if( preg_match( '/<body>(.*)<\/body>/is', $templateContent, $matches ) ){
            $template = $matches[1];
        }else{
            $template = $form->template;
        }

        $hiddenElems = "";
        foreach( $form->elements as $i => &$element ){
            if( $element->type == "hidden" ){
                //we will add hidden elements at end of form
                $hiddenElems .= $this->elementRenderer->render( $element )." ";
            }else{
                if( $element instanceof \X2Form\Collection ){
                    $template = str_replace( "[{$element->name}]", $this->collectionRenderer->render( $element  ), $template );
                }else{
                    $template = str_replace( "[{$element->name}]", $this->elementRenderer->render( $element  ), $template );
                }
                $template = str_replace( "[{$element->name}_label]", $element->label(), $template );
                $template = str_replace( "[{$element->name}_description]", $element->description(), $template );

            }
            $template = str_replace( "[{$element->name}_value]", $element->value, $template );
        }

        $attribs = '';
        foreach( $form->attributes as $key=>$atr ){
            $attribs .= " $key=\"$atr\"";
        }

        if( $addFormTag ){
            $template = "<form name=\"{$form->name}\" id=\"{$form->id}\" $attribs >$template $hiddenElems {$form->extraCode} </form>";
        }else{
            $template = "$template $hiddenElems {$form->extraCode}";
        }
        return $template;

    }

    public function raw( &$form ){
        $html = '';
        $cnt=1;
        $hiddenElems = '';

        foreach( $form->elements as $i => $element ){

            if( $element->type == 'hidden' ){
                $hiddenElems .= " [{$element->name}]";
            }elseif( $element->type == 'label' ){
                $html .= '<div class="form-group">'
                    ." [{$element->name}]"
                    .'</div>';
            }else{
                $cnt++;
                $html .= '<div class="form-group">'
                    ."[{$element->name}_label]"
                    ."[{$element->name}]"
                    .'<p class="help-block">['.$element->name.'_description]</p>'.
                    '</div>';
            }
        }

        return $html;
    }




} 