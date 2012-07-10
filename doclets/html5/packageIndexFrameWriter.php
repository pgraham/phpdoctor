<?php
/*
PHPDoctor: The PHP Documentation Creator
Copyright (C) 2004 Paul James <paul@peej.co.uk>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/** This generates the overview-frame.html file used for displaying the list
 * of package links in the upper-left frame in the frame-formatted default
 * output.
 *
 * @package PHPDoctor\Doclets\Zeptech
 */
class PackageIndexFrameWriter {
  
  private $_doclet;

	/** Build the package frame index.
	 *
	 * @param Doclet doclet
	 */
	function __construct($doclet) {
    $this->_doclet = $doclet;
  }

  public function generate($depth) {
    $docRoot = str_repeat('../', $depth);
    $idxLink = $docRoot . 'index.html';
		ob_start();
		
		echo '<h1>'.$this->_doclet->getHeader()."</h1>\n\n";
		
		echo "<ul>\n";
		echo "<li><a href=$idxLink>All Items</a></li>\n";
		echo "</ul>\n\n";
		
		echo "<h1>Namespaces</h1>\n\n";

		$rootDoc = $this->_doclet->rootDoc();

		echo "<ul>\n";

    $packages = $rootDoc->packages();
    ksort($packages);
		foreach($packages as $name => $package) {
      $packagePath = $docRoot . $package->asPath() . '/package-summary.html';
			echo "<li><a href=$packagePath>{$package->name()}</a></li>\n";
		}
		echo "</ul>\n\n";

		$output = ob_get_contents();
		ob_end_clean();

    return $output;
	}

}

?>
