<?php
namespace X2Form\Renderers\Bootstrap;

use X2Form\Collection;

class StepRenderer extends BasicRenderer{

    var $elementRenderer;

    public function render( &$step ){
        $html = '';
        if( $step->isActive ){
            $html = '<h3>'.$step->label.'</h3>';
            $html .= '<p>'.$step->description.'</p>';
            $html .= $this->elementRenderer->renderChildren( $step->elements,'vertical', false );
        }else{

        }
        return $html;
    }

    public function renderProgressBar( &$form ){
        
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