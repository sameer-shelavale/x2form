<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 7/16/15
 * Time: 10:58 AM
 */

namespace X2Form\Renderers\Bootstrap;


class MulticaptchaTheme {
    var $fieldClass = '';
    var $questionImageStyle = 'border-radius:3px; margin-bottom:5px;';
    var $questionTextStyle = 'font-size:120%; font-weight:bold; padding:4px;';
    var $questionAsciiStyle = 'background-color:#ccc; border-radius:3px; padding:4px;margin-bottom:2px;text-align:center;display:block;min-width:172px;';
    var $questionContainerStyle = '';

    var $helpBtnClass = 'btn-help';
    var $helpBtnText = '?';
    var $refreshBtnClass = 'btn-refresh';
    var $refreshBtnText = '&#8634;';
    var $extraHtml = <<<'EOT'
<style type="text/css">
a.btn-refresh, a.btn-help{
    border:0;
    text-decoration:none;
    vertical-align:top;
    margin-left:2px;
    display:inline-block;
    text-align:center;
    font-size:130%;
}
</style>
EOT;



    function __construct( $customValues=[] ){
        foreach( $customValues as $key => $val ){
            if( property_exists( get_class( $this ), $key ) ){
                $this->$key = $val;
            }
        }
    }


    function render( &$data, $refresh = false ){
        if( !isset( $data['containerId'] ) || !isset( $data['labelId'] ) ){
            $data['containerId'] = rtrim( base64_encode(uniqid('a',true)), '=' );
            $data['labelId'] = rtrim( base64_encode(uniqid('a',true)), '=' );
        }

        $html = '<div id="'.$data['containerId'].'" class="input-group">';
        $html .= $this->renderChallenge( $data );
        $html .= '<div class="input-group">';
        $html .= $this->renderResponseField( $data ).$this->renderTools( $data );
        $html .= '</div></div>';
        if( !$refresh ){
            $html .= $this->extraHtml;
            $html .= $this->renderRefreshScript( $data );
        }
        //$result['html'] = $html;

        //$result['label'] = '<span id="'.$data['labelId'].'">'.$this->renderLabel( $data ).'</span>';
        return $html;

    }

    function renderChallenge( $data ){
        $html = '';
        if( isset( $data['question'] ) && $data['question']['type'] == 'image' ){
            $html .= '<img src="'.$data['question']['content'].'" style="'.$this->questionImageStyle.'"/><br/>';
        }elseif( isset( $data['question'] ) && $data['question']['type'] == 'text' ){
            $html .= '<span style="'.$this->questionTextStyle.'">'.$data['question']['content'].'</span>';
        }elseif( isset( $data['question'] ) && $data['question']['type'] == 'ascii' ){
            $html .= '<span style="'.$this->questionAsciiStyle.'" >'.$data['question']['content'].'</span><br/>';
        }
        return $html;
    }

    function renderLabel( &$data ){
        if( !isset( $data['containerId'] ) || !isset( $data['labelId'] ) ) {
            $data['containerId'] = rtrim( base64_encode(uniqid(uniqid('a',true))), '=' );
            $data['labelId'] = rtrim( base64_encode(uniqid(uniqid('a',true))), '=' );
        }
        return '<span id="'.$data['labelId'].'" >'.$data['description']. '</span>';
    }

    function renderResponseField( $data ){
        $html = '<input type="text" name="'.$data['fieldName'].'" class="'.$this->fieldClass.'" ';
        if( isset( $data['tooltip'] ) ){
            $html .= $data['tooltip'];
        }
        $html .= '/>';
        if( isset( $data['hidden'] ) ){
            $html .= $data['hidden'];
        }
        return $html;
    }

    function renderTools( $data ){

        $help = '';
        if( isset( $data['helpUrl'] ) && strlen( $data['helpUrl'] ) > 0){
            $help = '<a href="'.$data['helpUrl'].'" target="_blank" title="Help" class="'.$this->helpBtnClass.'">'.$this->helpBtnText.'</a>';
        }

        $refresh = '';
        if( isset( $data['refreshUrl'] ) && strlen( $data['refreshUrl'] ) > 0 ){
            $refresh = '<a href="'.$data['refreshUrl'].'" title="Refresh"  class="'.$this->refreshBtnClass.'" onclick="return captcha_refresh(\''.$data['containerId'].'\', \''.$data['labelId'].'\');">'.$this->refreshBtnText.'</a>';
        }
        $html = $refresh.$help;
        if( $html != '' ){
            $html = '<div class="input-group-addon">'. $html. '</div>';
        }
        return  $html;
    }

    function renderRefreshScript( $data ){
        $script = <<<"EOT"
<script type="text/javascript">
    function captcha_refresh( c, l ) {
        var AJAX = null;
        if (window.XMLHttpRequest) {
            AJAX=new XMLHttpRequest();
        } else {
            AJAX=new ActiveXObject("Microsoft.XMLHTTP");
        }
        if (AJAX==null) {
            return false;
        }

        AJAX.onreadystatechange = function() {
            if (AJAX.readyState==4) {
                //update captcha html
                var result = JSON.parse( this.responseText );

                if( result.html && result.label ){
                    document.getElementById(l).outerHTML = result.label;
                    document.getElementById(c).outerHTML = result.html;
                    AJAX=null;
                }
            }
        }

        AJAX.open("GET", '{$data['refreshUrl']}', true);
        AJAX.send(null);
        return false;
    }

</script>
EOT;
        return $script;
    }


    /*
     * returns the data for refreshing the captcha array
     */
    function refresh( $data ){
        $result['html'] = $this->render( $data );
        $result['label'] = $this->renderLabel( $data );
        //$result['label'] = '<span id="'.$data['labelId'].'">'.$this->renderLabel( $data ).'</span>';
        return json_encode( $result );
    }
} 