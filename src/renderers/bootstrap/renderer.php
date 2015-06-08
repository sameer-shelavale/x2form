<?php
namespace X2Form\Renderers\Bootstrap;

use X2Form\Collection;

class Renderer implements \X2Form\Interfaces\Renderer{

    var $elementRenderer;
    var $collectionRenderer;

    function __construct(){
        $this->elementRenderer = new ElementRenderer();
        $this->collectionRenderer = new CollectionRenderer();
        $this->collectionRenderer->elementRenderer = &$this->elementRenderer;
    }

    public function render( &$form, $addFormTag=true ){

        if( $form->template && is_file( $form->template ) ){
            return $this->renderTemplate($form, $addFormTag);
        }

        //generate normal html
        $html = '';
        $cnt=1;
        $hiddenElems = '';

        foreach( $form->elements as $i=>$elem ){
            if( $cnt%2 == 0){ $class = 'even'; }else{ $class= 'odd'; }

            if( $elem->type == 'hidden' ){
                $hiddenElems .= $this->elementRenderer->render( $form->elements[$i] );
            }elseif( $elem->type == 'label' ){
                $html .= '<div class="form-group">'
                    .$this->elementRenderer->render( $form->elements[$i] )
                    .'</div>';
            }elseif( $form->elements[$i] instanceof \X2Form\Collection ){
                $cnt++;
                $html .= '<div class="form-group">'
                    .$form->elements[$i]->label()
                    .$this->collectionRenderer->render( $form->elements[$i] );

                if( strlen( $form->elements[$i]->description() ) > 0 ){
                    $html .= '<p class="help-block">'
                    .$form->elements[$i]->description()
                    .'</p>';
                }
                $html .= '</div>';

            }else{
                $cnt++;
                $html .= '<div class="form-group">'
                    .$this->elementRenderer->makeLabel( $form->elements[$i] )
                    .$this->elementRenderer->render( $form->elements[$i] );
                if( strlen( $form->elements[$i]->description() ) > 0 ){
                    $html .= '<p class="help-block">'
                    .$form->elements[$i]->description()
                    .'</p>';
                }
                $html .= '</div>';
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
        foreach( $form->elements as $i=>$elem ){
            if( $elem->type == "hidden" ){
                //we will add hidden elements at end of form
                $hiddenElems .= $this->elementRenderer->render( $form->elements[$i] )." ";
            }else{
                if( $form->elements[$i] instanceof \X2Form\Collection ){
                    $template = str_replace( "[{$elem->name}]", $this->collectionRenderer->render( $elem  ), $template );
                }else{
                    $template = str_replace( "[{$elem->name}]", $this->elementRenderer->render( $elem  ), $template );
                }
                $template = str_replace( "[{$elem->name}_label]", $elem->label(), $template );
                $template = str_replace( "[{$elem->name}_description]", $elem->description(), $template );

            }
            $template = str_replace( "[{$elem->name}_value]", $elem->value, $template );
        }

        $attribs = '';
        foreach( $this->attributes as $key=>$atr ){
            $attribs .= " $key=\"$atr\"";
        }

        if( $addFormTag ){
            $template = "<form name=\"{$form->name}\" id=\"{$form->id}\" $attribs >$template $hiddenElems {$this->extraCode} </form>";
        }else{
            $template = "$template $hiddenElems {$this->extraCode}";
        }
        return $template;

    }

    public function raw( &$form ){
        //generate normal html
        $html = '';
        $cnt=1;
        $hiddenElems = '';
        foreach( $form->elements as $elem ){
            if( $cnt%2 == 0){ $class = 'even'; }else{ $class= 'odd'; }
            if( $elem->type == 'hidden' ){
                $hiddenElems .= "[{$elem->name}]";
            }elseif( $elem->type == 'label' ){
                $html .= '<div class="form-group">'.$elem->label().$elem->render( $this->name ).' &nbsp; <i>'.$elem->description().'</i></div>';
            }else{
                $cnt++;
                $html .= '<div class="form-group">['.$elem->name.'_label]['.$elem->name.'] &nbsp; <i>['.$elem->name.'_description]</i></div>';
            }

        }

        $attribs = '';
        foreach( $this->attributes as $key=>$atr ){
            $attribs .= " $key=\"$atr\"";
        }

        $template = "<form name=\"{$this->name}\" id=\"{$this->id}\" $attribs >$html $hiddenElems {$this->extraCode} </form>";


        return $template;
    }




} 