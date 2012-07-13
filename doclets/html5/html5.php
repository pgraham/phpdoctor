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

// load classes
require('htmlWriter.php');
require('packageIndexWriter.php');
require('packageIndexFrameWriter.php');
require('packageFrameWriter.php');
require('packageWriter.php');
require('classWriter.php');
require('functionWriter.php');
require('globalWriter.php');
require('indexWriter.php');
require('deprecatedWriter.php');
require('todoWriter.php');
require('sourceWriter.php');

/** The standard doclet. This doclet generates HTML output similar to that
 * produced by the Javadoc standard doclet.
 *
 * @package PHPDoctor\Doclets\Zeptech
 */
class Html5 extends Doclet {

	/** A reference to the root doc.
	 *
	 * @var rootDoc
	 */
	private $_rootDoc;

	/** The directory to place the generated files.
	 *
	 * @var str
	 */
	private $_d;

	/** Specifies the title to be placed in the HTML <title> tag.
	 *
	 * @var str
	 */
	private $_windowTitle = 'The Unknown Project';

	/** Specifies the title to be placed near the top of the overview summary
	 * file.
	 *
	 * @var str
	 */
	private $_docTitle = 'The Unknown Project';

	/** Specifies the header text to be placed at the top of each output file.
	 * The header will be placed to the right of the upper navigation bar.
	 *
	 * @var str
	 */
	private $_header = 'Unknown';

	/** Specifies the footer text to be placed at the bottom of each output file.
	 * The footer will be placed to the right of the lower navigation bar.
	 *
	 * @var str
	 */
	private $_footer = 'Unknown';

	/** Specifies the text to be placed at the bottom of each output file. The
	 * text will be placed at the bottom of the page, below the lower navigation
	 * bar.
	 *
	 * @var str
	 */
	private $_bottom = 'This document was generated by <a href="http://peej.github.com/phpdoctor/">PHPDoctor: The PHP Documentation Creator</a>';

	/** Create a class tree?
	 *
	 * @var str
	 */
	private $_tree = true;
	
	/** Whether or not to parse the code with GeSHi and include the formatted files
	 * in the documentation.
	 *
	 * @var boolean
	 */
	private $_includeSource = true;

	/** Doclet constructor.
	 *
	 * @param RootDoc rootDoc
	 * @param TextFormatter formatter
	 */
	public function __construct($rootDoc, $formatter) {
	
		// set doclet options
		$this->_rootDoc = $rootDoc;
		$phpdoctor = $rootDoc->phpdoctor();
		$options = $rootDoc->options();
		
		$this->formatter = $formatter;
		
		if (isset($options['d'])) {
			$this->_d = $phpdoctor->makeAbsolutePath($options['d'], $phpdoctor->sourcePath());
		} elseif (isset($options['output_dir'])) {
			$this->_d = $phpdoctor->makeAbsolutePath($options['output_dir'], $phpdoctor->sourcePath());
		} else {
			$this->_d = $phpdoctor->makeAbsolutePath('apidocs', $phpdoctor->sourcePath());
		}
		$this->_d = $phpdoctor->fixPath($this->_d);
		
		if (is_dir($this->_d)) {
			$phpdoctor->warning('Output directory already exists, overwriting');
		} else {
			mkdir($this->_d);
		}
		$phpdoctor->verbose('Setting output directory to "'.$this->_d.'"');
		
		if (isset($options['windowtitle'])) $this->_windowTitle = $options['windowtitle'];
		if (isset($options['doctitle'])) $this->_docTitle = $options['doctitle'];
		if (isset($options['header'])) $this->_header = $options['header'];
		if (isset($options['footer'])) $this->_footer = $options['footer'];
		if (isset($options['bottom'])) $this->_bottom = $options['bottom'];

		if (isset($options['tree'])) $this->_tree = $options['tree'];
		
		if (isset($options['include_source'])) $this->_includeSource = $options['include_source'];
        if ($this->_includeSource) {
            @include_once 'geshi/geshi.php';
            if (!class_exists('GeSHi')) {
                $phpdoctor->warning('Could not find GeSHi in "geshi/geshi.php", not pretty printing source');
            }
		}
		
		// write frame
		//$frameOutputWriter = new frameOutputWriter($this);

		// write overview summary
		$packageIndexWriter = new packageIndexWriter($this);

		// write package overview frame
		//$packageIndexFrameWriter = new packageIndexFrameWriter($this);

		// write package summaries
		$packageWriter = new packageWriter($this);
		
		// write package frame
		//$packageFrameWriter = new packageFrameWriter($this);
        
		// write classes
		$classWriter = new classWriter($this);
		
		// write global functions
		$functionWriter = new functionWriter($this);
		
		// write global variables
		$globalWriter = new globalWriter($this);

		// write index
		$indexWriter = new indexWriter($this);
        
		// write deprecated index
		$deprecatedWriter = new deprecatedWriter($this);
		
		// write todo index
		$todoWriter = new todoWriter($this);
		
		// write source files
		if ($this->_includeSource) {
            $sourceWriter = new sourceWriter($this);
		}
		
		// copy stylesheets
		$phpdoctor->message('Copying stylesheets');
		copy($phpdoctor->docletPath().'reset.css', $this->_d.'reset.css');
		copy($phpdoctor->docletPath().'stylesheet.css', $this->_d.'stylesheet.css');

    // Copy background image
    $phpdoctor->message('Copying background image');
    $imgSrc = $phpdoctor->docletPath() . 'img/api-bg.png';
    $imgOutDir = $this->_d . 'img';
    $imgOut = "$imgOutDir/api-bg.png";
    mkdir($imgOutDir, 0755, true);
    copy($imgSrc, $imgOut);
	
	}

	/** Return a reference to the root doc.
	 *
	 * @return RootDoc
	 */
	function &rootDoc() {
		return $this->_rootDoc;
	}
	
	/** Return a reference to the application object.
	 *
	 * @return PHPDoctor
	 */
	function &phpdoctor() {
		return $this->_rootDoc->phpdoctor();
	}

	/** Get the destination path to write the doclet output to.
	 *
	 * @return str
	 */	
	function destinationPath()
    {
		return $this->_d;
	}

	/** Return the title to be placed in the HTML <title> tag.
	 *
	 * @return str
	 */
	function windowTitle()
    {
		return $this->_windowTitle;
	}

	/** Return the title to be placed near the top of the overview summary
	 * file.
	 *
	 * @return str
	 */
	function docTitle()
    {
		return $this->_docTitle;
	}

	/** Return the header text to be placed at the top of each output file.
	 * The header will be placed to the right of the upper navigation bar.
	 *
	 * @return str
	 */
	function getHeader()
    {
		return $this->_header;
	}

	/** Return the footer text to be placed at the bottom of each output file.
	 * The footer will be placed to the right of the lower navigation bar.
	 *
	 * @return str
	 */
	function getFooter()
    {
		return $this->_footer;
	}

	/** Return the text to be placed at the bottom of each output file. The
	 * text will be placed at the bottom of the page, below the lower navigation
	 * bar.
	 *
	 * @return str
	 */
	function bottom()
  {
		return $this->_bottom;
	}

	/** Return whether to create a class tree or not.
	 *
	 * @return bool
	 */
	function tree()
  {
		return $this->_tree;
	}
	
	/** Should we be outputting the source code?
	 *
	 * @return bool
	 */
	function includeSource()
	{
    return $this->_includeSource;
	}

  /**
   * Format a URL link
   *
   * @param str url
   * @param str text
   */
  function formatLink($url, $text)
  {
    return '<a href="'.$url.'">'.$text.'</a>';
  }
  
  /**
   * Format text as a piece of code
   *
   * @param str text
   * @return str
   */
  function asCode($text)
  {
    return '<code>'.$text.'</code>';
  }
    
}
