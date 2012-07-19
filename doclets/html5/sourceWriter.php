<?php
/*
PHPDoctor: The PHP Documentation Creator
Copyright (C) 2010 Paul James <paul@peej.co.uk>

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

/** This uses GeSHi to generate formatted source for each source file in the
 * parsed code.
 *
 * @package PHPDoctor\Doclets\Zeptech
 */
class SourceWriter extends HTMLWriter
{

  private static $_ESCAPE_CODE_OPTS; // = ENT_QUOTES | ENT_HTML5;
  
  private static function _init() {
    if (self::$_ESCAPE_CODE_OPTS === null) {
      self::$_ESCAPE_CODE_OPTS = ENT_QUOTES; // | ENT_HTML5; <- php 5.4
    }
  }

	/** Parse the source files.
	 *
	 * @param Doclet doclet
	 */
	public function __construct(&$doclet) {
		parent::__construct($doclet);
    self::_init();
		
		$rootDoc =& $this->_doclet->rootDoc();
		$phpdoctor =& $this->_doclet->phpdoctor();
		
    $this->_buildSections($phpdoctor);
        
		$sources =& $rootDoc->sources();
        
    $this->_id = 'files';
    
    ob_start();
    
    echo '<h1>Source Files</h1>';
    
    echo "<ul>\n";
    foreach ($sources as $filename => $data) {
      $url = strtolower(str_replace(DIRECTORY_SEPARATOR, '/', $filename));
      echo '<li><a href="source/', $url, '.html">', $filename, '</a></li>';
    }
    echo "</ul>\n";
    
    $this->_output = ob_get_contents();
    ob_end_clean();
    
    $this->write('overview-files.html', 'Overview');
    	
        
		$this->_id = 'file';
		
		foreach ($sources as $filename => $data) {
      $this->_depth = substr_count($filename, '/') + 1;
      $this->_buildSections($phpdoctor, $filename);
      
      if (class_exists('GeSHi')) {
        $geshi = new GeSHi($data[0], 'php');
        $source = $geshi->parse_code();

        $lines = array();
        foreach (explode("\n", $source) as $index => $line) {
          $lines[] = '<span id="line'.($index + 1).'"></span>' . $line . "\n";
        }
        $source = implode("\n", $lines);
      } else {
        $code = $this->_escapeCode($data[0]);
        $lines = explode("\n", $code);
        $numbered = array();
        foreach ($lines as $idx => $line) {
          $lineNum = $idx + 1;
          $numbered[] = "<div id=line$lineNum class=code-line><span class=line-number>$lineNum</span>$line</div>";
        }
        $source = '<pre class=source-code><code>'. implode('', $numbered) . '</code></pre>';
      }
      
      ob_start();
      
      echo '<h1>'.$filename."</h1>\n";
      
      if (isset($data[1]['tags']['@text'])) {
        echo '<div class="comment" id="overview_description">', $this->_processInlineTags($data[1]['tags']['@text']), "</div>\n\n";
      }
      
      echo "<hr>\n\n";

      echo $source;
      
      $this->_output = ob_get_contents();
      ob_end_clean();
      
      $this->write('source/'.strtolower($filename).'.html', $filename);
            
		}
  }

  private function _buildSections($phpdoctor, $filename = null) {
    $this->_sections = array();
    $this->_sections[] = array('title' => 'Overview', 'url' => 'index.html');
    if ($filename !== null) {
      $path = str_replace(DIRECTORY_SEPARATOR, '/', dirname($filename));
      $htmlfilename = strtolower(basename($filename, '.php')) . '.html';
      $this->_sections[] = array(
        'title' => 'Namespace',
        'url' => "$path/$htmlfilename"
      );
    } else {
      $this->_sections[] = array('title' => 'Namespace');
    }
    $this->_sections[] = array('title' => 'Class');
    if ($phpdoctor->getOption('tree')) {
      $this->_sections[] = array('title' => 'Tree', 'url' => 'overview-tree.html');
    }
    $this->_sections[] = array('title' => 'Files', 'url' => 'overview-files.html', 'selected' => TRUE);
    $this->_sections[] = array('title' => 'Deprecated', 'url' => 'deprecated-list.html');
    $this->_sections[] = array('title' => 'Todo', 'url' => 'todo-list.html');
    $this->_sections[] = array('title' => 'Index', 'url' => 'index-all.html');
  }

  private function _escapeCode($code) {
    return htmlspecialchars($code, self::$_ESCAPE_CODE_OPTS, 'UTF-8', false);
  }
}
