<?php
namespace X2Form\Renderers\Table;

use X2Form\Collection;

class GroupRenderer extends BasicRenderer{

    var $elementRenderer;
    var $collectionRenderer;

    public function render( &$group ){

        if( $group->direction == 'inline' ){
            //horizontal alignment using blank space as seperator
            $hiddenElems = '';
            $html = '';
            foreach( $group->elements as $i => &$element ){
                if( $element instanceof \X2Form\Collection ){
                    $html .= $this->makeLabel( $element ).' '
                        .$this->collectionRenderer->render( $element ).' '
                        .$this->makeDescription( $element ).' &nbsp; ';
                }elseif( $element instanceof \X2Form\Group ){
                    $html .= $this->makeLabel( $element ).' '
                        .$this->render( $element ).' '
                        .$this->makeDescription( $element ).' &nbsp; ';
                }elseif( $element->type == 'hidden' ){
                    $hiddenElems .= $this->elementRenderer->render( $element );
                }elseif( $element->type == 'label' ){
                    $html .= $this->elementRenderer->render( $element ).' &nbsp; ';
                }else{
                    $html .= $this->makeLabel( $element ).' '
                        .$this->elementRenderer->render( $element ).'  '
                        .$this->makeDescription( $element ).' &nbsp; ';
                }
            }
            $html .= $hiddenElems;

        }elseif( $group->direction == 'horizontal' ){
            //horizontal alignment using tables
            //generate normal html
            $html = '<table cellpadding="0" cellspacing="0" border="0"><tr>';
            $hiddenElems = '';

            foreach( $group->elements as $i => &$element ){
                if( $element instanceof \X2Form\Collection ){
                    $html .= '<td valign="top">'
                        .$this->makeLabel( $element ).'</td><td>'
                        .$this->collectionRenderer->render( $element ).' &nbsp; '
                        .$this->makeDescription( $element )
                        .'</td>';
                }elseif( $element instanceof \X2Form\Group ){
                    $html .= '<td valign="top">'
                        .$this->makeLabel( $element ).'</td><td>'
                        .$this->render( $element ).' &nbsp; '
                        .$this->makeDescription( $element )
                        .'</td>';
                }elseif( $element->type == 'hidden' ){
                    $hiddenElems .= $this->elementRenderer->render( $element );
                }elseif( $element->type == 'label' ){
                    $html .= '<td valign="top" colspan="2">'
                        .$this->elementRenderer->render( $element ).' &nbsp;</td>';
                }else{
                    $html .= '<td valign="top">'
                        .$this->makeLabel( $element ).'</td><td>'
                        .$this->elementRenderer->render( $element ).' &nbsp; '
                        .$this->makeDescription( $element )
                        .'</td>';
                }
            }
            $html .= '</tr></table>'.$hiddenElems;

        }else{
            // vertical alignment using tables
            //generate normal html
            $html = '<table cellpadding="0" cellspacing="0" border="0">';
            $cnt=1;
            $hiddenElems = '';

            foreach( $group->elements as $i => &$element ){
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
                        .$this->render( $element ).' &nbsp; '
                        .$this->makeDescription( $element ).'</td></tr>';
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

            $html .= '</table>'.$hiddenElems;
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