<?php
namespace X2Form\Renderers\Table;

class CollectionRenderer extends BasicRenderer{
    var $elementRenderer;
    var $groupRenderer;


    /*****************************************************************************
     * function render()
     * 		renders the Collection HTML.
     * returns:
     * 		the rendered HTML as string.
     * 		Note that it does'nt send output to screen
     ****************************************************************************/
    public function render( &$collection ){

        $collection->finalize();

        $html = $this->renderList( $collection );

        $collection->schema->index = 'X2F_INDEX';
        $addHtml = '<tr>';
        foreach( $collection->schema->elements as $i => $elem ){
            $addHtml .= '<td>'.$this->elementRenderer->render( $elem ).'</td>';
        }
        $addHtml .= '<td style="width:1em;">'.$this->renderDeleteBtn( $collection ).'</td>';
        $addHtml .= '</tr>';

        $html .= '
		<script type="text/javascript">
			var  '.$this->makeName( $collection ).'_count = '.$collection->elements->count().';
			function '.$this->makeAddFunctionName($collection) .'(){
				var tmp = \''.$addHtml. '\';
				tmp = tmp.replace( /X2F_INDEX/gi, '.$this->makeName( $collection ).'_count );
				$(\'#'.$this->makeName( $collection ).'_list tr:last\').after(tmp);
				'.$this->makeName( $collection ).'_count ++;
			}
		</script>';

        return $html;
    }

    public function renderTemplate(){

    }

    public function renderRaw(){

    }


    /*****************************************************************************
     * function renderList()
     * 		renders a list of field sets in the Collection, with the fields
     * 		arranged horizontally in a row.
     * 		Generally this is called by render() function.
     * returns:
     * 		the rendered HTML as string.
     * 		Note that it does'nt send output to screen
     ****************************************************************************/
    public function renderList( &$collection ){

        $html = '<table class="table table-hover table-striped" id="'.$this->makeName( $collection ).'_list">';

        $html .= $this->renderListHeader( $collection );
        foreach( $collection->elements as $i => &$subForm ){
            $html .= '<tr>';
            foreach( $subForm->elements as &$element ){
                $html .= '<td>'.$this->elementRenderer->render( $element ).'</td>';
            }
            $html .= '<td style="width:1em;">'.$this->renderDeleteBtn( $collection ).'</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }


    /*****************************************************************************
     * function renderListHeader()
     * 		renders a row of column titles for the list generated by renderList().
     * 		Generally this is called by renderList() function.
     * returns:
     * 		the rendered HTML as string.
     * 		Note that it does'nt send output to screen
     ****************************************************************************/
    public function renderListHeader( &$collection ){
        $header = '<tr>';
        foreach( $collection->schema->elements as &$element ){
            $header .= '<th>'.$element->label().'</th>';
        }
        $header .= '<th style="width:1em;">'.$this->renderAddBtn( $collection).'</th>';
        $header .= '</tr>';
        return $header;
    }

    public function renderListRaw(){

    }

    public function renderListTemplate(){

    }

    public function renderAddBtn( &$collection  ){
        $btn = '<button type="button" class="btn btn-add btn-success btn-xs" title="Add" onclick="'.$this->makeAddFunctionName( $collection).'()" >'
            .'<span>+</span>'
            .'</button>';
        return $btn;
    }

    public function renderDeleteBtn( &$collection  ){
        $btn = '<button type="button" class="btn btn-delete btn-danger btn-xs" title="Remove" onclick="this.parentElement.parentElement.remove()" >'
            .'<span>&times;</span>'
            .'</button>';
        return $btn;
    }

    public function makeAddFunctionName( &$collection ){
        return 'AddToX2'.$this->makeName( $collection ).'_list';
    }

    public function makeName( &$collection ){
        return $collection->parent->name.'_'.$collection->name;
    }
}