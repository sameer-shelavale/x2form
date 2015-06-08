<?php
/*******************************************************************************************************
 * class X2FormElement
 * X2FormElement is used by X2Form for generating different HTML form elements.
 * 
 * Planned Features:
 * 		1. Multiple File upload
 * 		2. Image Handeling/Resizing/Resampling
 * 
 * Author : Sameer Shelavale
 * Email  : samiirds@gmail.com, sameer@techrevol.com, sameer@possible.in 
 * Author website: http://techrevol.com, http://possible.in
 * Phone  : +91 9890103122
 * License: AGPL3, You should keep Package name, Class name, Author name, Email and website credits.
 * 			http://www.gnu.org/licenses/agpl-3.0.html
 * 			For other type of licenses please contact the author.
 * PHP Version: Tested on PHP 5.2.2 & 5.3.10
 * Dependencies : X2FormElement.php, class.dbhelper.php
 * Copyrights (C) 2012-2015 Sameer Shelavale
 * Dependencies : class.dbhelper.php, class.logg.php
 *******************************************************************************************************/
namespace X2Form\Interfaces;

interface Renderer{

	public function render( &$form, $addFormTag=true );

    public function renderTemplate( &$element );

    public function raw( &$element );

}

?>