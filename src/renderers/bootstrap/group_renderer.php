<?php
namespace X2Form\Renderers\Bootstrap;

use X2Form\Collection;

class GroupRenderer extends BasicRenderer{

    var $elementRenderer;
    var $collectionRenderer;


    public function render( &$group ){

        if( $group->direction == 'inline' ){
            //horizontal alignment using blank space as seperator
            $hiddenElems = '';
            $html = '<div class="form-group form-inline">';
            foreach( $group->elements as $i => &$element ){
                if( $element instanceof \X2Form\Collection ){
                    $html .= $this->makeLabel( $element ).' '
                        .$this->collectionRenderer->render( $element )
                        .$this->makeDescription( $element ).' ';
                }elseif( $element instanceof \X2Form\Group ){
                    $html .=  $this->makeLabel( $element ).' '
                        .$this->render( $element ).' '
                        .$this->makeDescription( $element ).' ';
                }elseif( $element->type == 'hidden' ){
                    $hiddenElems .= $this->elementRenderer->render( $element );
                }elseif( $element->type == 'label' ){
                    $html .= $this->elementRenderer->render( $element ).' ';
                }elseif( in_array( $element->type, ['submit', 'reset', 'password', 'image'] ) ){
                    $html .= $this->elementRenderer->render( $element ).' ';
                }else{
                    $html .= $this->makeLabel( $element ).' '
                        .$this->elementRenderer->render( $element ).' '
                        .$this->makeDescription( $element ).' ';
                }
            }
            $html .= $hiddenElems.'</div> ';

        }elseif( $group->direction == 'horizontal' ){
            //horizontal alignment using blank space as seperator
            //horizontal alignment using blank space as seperator
            $hiddenElems = '';
            $html = '<div class="form-group form-inline">';
            foreach( $group->elements as $i => &$element ){
                if( $element instanceof \X2Form\Collection ){
                    $html .= '<div class="form-group">'
                        .$this->makeLabel( $element )
                        .$this->collectionRenderer->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }elseif( $element instanceof \X2Form\Group ){
                    $html .=  '<div class="form-group">'
                        .$this->makeLabel( $element )
                        .$this->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }elseif( $element->type == 'hidden' ){
                    $hiddenElems .= $this->elementRenderer->render( $element );
                }elseif( $element->type == 'label' ){
                    $html .= '<div class="form-group">'
                        .$this->elementRenderer->render( $element )
                        .'</div> ';
                }else{
                    $html .= '<div class="form-group">'
                        .$this->makeLabel( $element ).' '
                        .$this->elementRenderer->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }
            }
            $html .= $hiddenElems.'</div> ';

        }else{
            // vertical alignment using tables
            //generate normal html
            $hiddenElems = '';
            $html = '<div class="form-group">';
            foreach( $group->elements as $i => &$element ){
                if( $element instanceof \X2Form\Collection ){
                    $html .= '<div class="form-group">'
                        .$this->makeLabel( $element )
                        .$this->collectionRenderer->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }elseif( $element instanceof \X2Form\Group ){
                    $html .=  '<div class="form-group">'
                        .$this->makeLabel( $element )
                        .$this->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }elseif( $element->type == 'hidden' ){
                    $hiddenElems .= $this->elementRenderer->render( $element );
                }elseif( $element->type == 'label' ){
                    $html .= '<div class="form-group">'
                        .$this->elementRenderer->render( $element )
                        .'</div> ';
                }else{
                    $html .= '<div class="form-group">'
                        .$this->makeLabel( $element ).' '
                        .$this->elementRenderer->render( $element )
                        .$this->makeDescription( $element )
                        .'</div> ';
                }
            }
            $html .= $hiddenElems.'</div> ';
        }
        return $html;
    }


    /*****************************************************************************
     * function renderTemplate()
     * 		To be done
     ****************************************************************************/
    function renderTemplate( &$form, $addFormTag= true ){

    }

    /*****************************************************************************
     * function renderTemplate()
     * 		To be done
     ****************************************************************************/
    public function raw( &$form ){

    }

} 