<?php
namespace X2Form\Renderers\Table;

use X2Form\Collection;
use X2Form\Group;

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

    public function render( &$form, $addFormTag = true ){

        if( $form->template && is_file( $form->template ) ){
            return $this->renderTemplate( $form, $addFormTag );
        }

        //generate normal html
        $html = '<table cellpadding="0" cellspacing="0" border="0">';
        $cnt=1;
        $hiddenElems = '';

        foreach( $form->elements as $i => &$element ){
            if( $cnt%2 == 0){ $class = 'even'; }else{ $class= 'odd'; }

            if( $element instanceof \X2Form\Collection ){
                $cnt++;
                $html .= '<tr class="'.$class.'"><td valign="top">'
                    .$this->makeLabel( $element ).'</td><td>'
                    .$this->collectionRenderer->render( $element ).' &nbsp; '
                    .$this->makeDescription( $element )
                    .'</td></tr>';
            }elseif( $element instanceof \X2Form\Group ){
                $cnt++;
                $html .= '<tr class="'.$class.'"><td valign="top">'
                    .$this->makeLabel( $element ).'</td><td>'
                    .$this->groupRenderer->render( $element ).' &nbsp; '
                    .$this->makeDescription( $element )
                    .'</td></tr>';
            }elseif( $element->type == 'hidden' ){
                $hiddenElems .= $this->elementRenderer->render( $element );
            }elseif( $element->type == 'label' ){
                $html .= '<tr class="'.$class.'"><td valign="top" colspan="2">'
                    .$this->elementRenderer->render( $element ).' &nbsp;</td></tr>';
            }else{
                $cnt++;
                $html .= '<tr class="'.$class.'"><td valign="top">'
                    .$this->makeLabel( $element ).'</td><td>'
                    .$this->elementRenderer->render( $element ).' &nbsp; '
                    .$this->makeDescription( $element ).'</td></tr>';
            }
        }

        $html .= '</table>';

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
        foreach( $form->elements as $i => $element ){
            if( $element->type == "hidden" ){
                //we will add hidden elements at end of form
                $hiddenElems .= $this->elementRenderer->render( $element )." ";
            }else{
                if( $element instanceof \X2Form\Collection ){
                    $template = str_replace( "[{$element->name}]", $this->collectionRenderer->render( $element  ), $template );
                }elseif( $element instanceof \X2Form\Group ){
                    $template = str_replace( "[{$element->name}]", $this->groupRenderer->render( $element  ), $template );
                }else{
                    $template = str_replace( "[{$element->name}]", $this->elementRenderer->render( $element  ), $template );
                }
                $template = str_replace( "[{$element->name}_label]", $this->elementRenderer->makeLabel( $element ), $template );
                $template = str_replace( "[{$element->name}_description]", $element->description(), $template );

            }


            if( is_array( $element->value )){
                $template = str_replace( "[{$element->name}_value]", implode( ', ', $element->value ), $template );
            }else{
                $template = str_replace( "[{$element->name}_value]", $element->value, $template );
            }
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
        //generate normal html
        //generate normal html
        $html = '<table cellpadding="0" cellspacing="0" border="0">';
        $cnt=1;
        $hiddenElems = '';

        foreach( $form->elements as $i => $element ){
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

} 