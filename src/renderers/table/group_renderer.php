<?php
namespace X2Form\Renderers\Table;

use X2Form\Collection;

class GroupRenderer extends BasicRenderer{

    var $elementRenderer;

    public function render( &$group ){
        return $this->elementRenderer->renderChildren( $group->elements, $group->direction );
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