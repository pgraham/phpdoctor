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

/** This generates the index.html file used for presenting the frame-formated
 * "cover page" of the API documentation.
 *
 * @package PHPDoctor\Doclets\Zeptech
 */
class HTMLWriter
{

	/** The doclet that created this object.
	 *
	 * @var doclet
	 */
	protected $_doclet;

	/** The section titles to place in the header and footer.
	 *
	 * @var str[][]
	 */
	protected $_sections = array();

	/** The directory structure depth. Used to calculate relative paths.
	 *
	 * @var int
	 */
	protected $_depth = 0;

	/** The <body> id attribute value, used for selecting style.
	 *
	 * @var str
	 */
	protected $_id = 'overview';

	/** The output body.
	 *
	 * @var str
	 */
	protected $_output = '';

	/** Writer constructor.
	 */
	public function __construct($doclet) {
		$this->_doclet = $doclet;
	}

  protected function asTopLevelPath($path) {
    return str_repeat('../', $this->_depth) . $path;
  }

	/** Build the HTML header. Includes doctype definition, <html> and <head>
	 * sections, meta data and window title.
	 *
	 * @return str
	 */
	function _htmlHeader($title) {

    $phpDocVersion = htmlspecialchars('PHPDoctor ' . PHPDoctor::VERSION,
      ENT_QUOTES, 'UTF-8', false /* Don't double quote */);

    $resetCss = $this->asTopLevelPath('reset.css');
    $styleCss = $this->asTopLevelPath('stylesheet.css');
    $indexPath = $this->asTopLevelPath('index.html');
	
		$output = $this->_doctype();
		$output .= "<html lang=en>\n";
		$output .= "<head>\n\n";
		
		$output .= "<meta charset=UTF-8>\n";
		$output .= "<meta name=generator content=$phpDocVersion>\n";
		$output .= "<meta name=when content=" .gmdate('r') .">\n\n";

    $output .= "<link rel=stylesheet type=text/css href=http://fonts.googleapis.com/css?family=Comfortaa:400,700|Exo:400,700|Ubuntu+Mono>\n";
		
		$output .= "<link rel=stylesheet href=$resetCss>\n";
		$output .= "<link rel=stylesheet href=$styleCss>\n";
		$output .= "<link rel=start href=$indexPath>\n\n";
		
		$output .= '<title>';
		if ($title) {
			$output .= $title.' ('.$this->_doclet->windowTitle().')';
		} else {
			$output .= $this->_doclet->windowTitle();
		}
		$output .= "</title>\n\n";

		$output .= "</head>\n";

		return $output;

	}
    
    /** Get the HTML DOCTYPE for this output
     *
     * @return str
     */
    function _doctype()
    {
        return "<!DOCTYPE html>\n";
    }
	
	/** Build the HTML footer.
   *
   * @return str
   */
	function _htmlFooter()
    {
		return '</html>';
	}

	/** Build the HTML shell header. Includes beginning of the <body> section,
	 * and the page header.
	 *
	 * @return str
	 */
	private function _shellHeader($path, $package = null) {
		$output = "<body>\n";

    $output .= "<nav id=packages>\n";
    $output .= $this->_packages();
    $output .= "</nav>\n";

    $output .= "<nav id=all-items>\n";
    $output .= $this->_allItems($package);
    $output .= "</nav>\n";

		$output .= $this->_nav($path);
    $output .= "<section id=content>\n";

		return $output;
	}

  private function _packages($selectedPackage = null) {
    $packagesGenerator = new packageIndexFrameWriter($this->_doclet);
    return $packagesGenerator->generate($this->_depth);
  }

  private function _allItems($package = null) {
    $allItemsGenerator = new packageFrameWriter($this->_doclet);
    return $allItemsGenerator->generate($this->_depth, $package);
  }
	
	/** Build the HTML shell footer. Includes the end of the <body> section, and
	 * page footer.
	 *
	 * @return str
	 */
	function _shellFooter($path)
    {
    $output = "</section>\n";
		$output .= '<footer id="footer">'.$this->_doclet->bottom().'</footer>'."\n\n";
		$output .= "</body>\n\n";
		return $output;
	}
	
	/** Build the navigation bar
	 *
	 * @return str
	 */
	function _nav($path) {
    $thisClass = strtolower(get_class($this));

		$output = "<nav id=main-nav>\n";
		$output .= "<h1>{$this->_doclet->getHeader()}</h1>\n";
		if ($this->_sections) {
      $output .= "<ul>\n";
			foreach ($this->_sections as $section) {
				if (isset($section['selected']) && $section['selected']) {
					$output .= '<li class="active">'.$section['title']."</li>\n";
				} else {
					if (isset($section['url'])) {
						$output .= '<li><a href="'.str_repeat('../', $this->_depth).$section['url'].'">'.$section['title']."</a></li>\n";
					} else {
						$output .= '<li>'.$section['title'].'</li>';
					}
				}
			}
            $output .= "</ul>\n";
		}


		if ($thisClass == 'classwriter') {
			$output .= '<div class="small_links">'."\n";
			$output .= 'Summary: <a href="#summary_field">Field</a> | <a href="#summary_method">Method</a> | <a href="#summary_constr">Constr</a>'."\n";
			$output .= 'Detail: <a href="#detail_field">Field</a> | <a href="#detail_method">Method</a> | <a href="#summary_constr">Constr</a>'."\n";
			$output .= "</div>\n";
		} elseif ($thisClass == 'functionwriter') {
			$output .= '<div class="small_links">'."\n";
			$output .= 'Summary: <a href="#summary_function">Function</a>'."\n";
			$output .= 'Detail: <a href="#detail_function">Function</a>'."\n";
			$output .= "</div>\n";
		} elseif ($thisClass == 'globalwriter') {
			$output .= '<div class="small_links">'."\n";
			$output .= 'Summary: <a href="#summary_global">Global</a>'."\n";
			$output .= 'Detail: <a href="#detail_global">Global</a>'."\n";
			$output .= "</div>\n";
		}
		$output .= "</nav>\n\n";

		return $output;
	}
	
	function _sourceLocation($doc)
	{
	    if ($this->_doclet->includeSource()) {
	        $url = strtolower(str_replace(DIRECTORY_SEPARATOR, '/', $doc->sourceFilename()));
	        echo '<a href="', str_repeat('../', $this->_depth), 'source/', $url, '.html#line', $doc->sourceLine(), '" class="location">', $doc->location(), "</a>\n\n";
	    } else {
	        echo '<div class="location">', $doc->location(), "</div>\n";
	    }
	}

	/** Write the HTML page to disk using the given path.
	 *
	 * @param str path The path to write the file to
	 * @param str title The title for this page
	 * @param bool shell Include the page shell in the output
	 */
	protected function write($path, $title, $package = null) {
		$phpdoctor = $this->_doclet->phpdoctor();

    if ($package) {
      $path = $package->asPath() . DIRECTORY_SEPARATOR . $path;
    }
		
		// make directory separators suitable to this platform
		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		
		// make directories if they don't exist
		$dirs = explode(DIRECTORY_SEPARATOR, $path);
		array_pop($dirs);
		$testPath = $this->_doclet->destinationPath();
		foreach ($dirs as $dir) {
			$testPath .= $dir.DIRECTORY_SEPARATOR;
			if (!is_dir($testPath)) {
        if (!@mkdir($testPath)) {
          $phpdoctor->error("Could not create directory: $testPath");
          exit;
        }
      }
		}
		
		// write file
		$fp = fopen($this->_doclet->destinationPath().$path, 'w');
		if ($fp) {
			$phpdoctor->message('Writing "'.$path.'"');
			fwrite($fp, $this->_htmlHeader($title));
			fwrite($fp, $this->_shellHeader($path, $package));
			fwrite($fp, $this->_output);
			fwrite($fp, $this->_shellFooter($path));
			fwrite($fp, $this->_htmlFooter());
			fclose($fp);
		} else {
			$phpdoctor->error('Could not write "'.$this->_doclet->destinationPath().$path.'"');
            exit;
		}
	}
	
	/** Format tags for output.
	 *
	 * @param Tag[] tags
	 * @return str The string representation of the elements doc tags
	 */
	function _processTags($tags)
    {
		$tagString = '';
		foreach ($tags as $key => $tag) {
			if ($key != '@text') {
			    if (is_array($tag)) {
				    $hasText = false;
                    foreach ($tag as $key => $tagFromGroup) {
                        if ($tagFromGroup->text($this->_doclet) != '') {
                            $hasText = true;
                        }
                    }
                    if ($hasText) {
                        $tagString .= '<dt>'.$tag[0]->displayName().":</dt>\n";
                        foreach ($tag as $tagFromGroup) {
                            $tagString .= '<dd>'.$tagFromGroup->text($this->_doclet)."</dd>\n";
                        }
                    }
				} else {
			        $text = $tag->text($this->_doclet);
			        if ($text != '') {
						$tagString .= '<dt>'.$tag->displayName().":</dt>\n";
						$tagString .= '<dd>'.$text."</dd>\n";
					} elseif ($tag->displayEmpty()) {
						$tagString .= '<dt>'.$tag->displayName().".</dt>\n";
					}
				}
			}
		}
        if ($tagString) {
            echo "<dl>\n", $tagString, "</dl>\n";
        }
	}
	
	/** Convert inline tags into a string for outputting.
	 *
	 * @param Tag tag The text tag to process
	 * @param bool first Process first line of tag only
	 * @return str The string representation of the elements doc tags
	 */
	function _processInlineTags($tag, $first = false)
    {
        $description = '';
        if (is_array($tag)) $tag = $tag[0];
        if (is_object($tag)) {
            if ($first) {
                $tags = $tag->firstSentenceTags($this->_doclet);
            } else {
                $tags = $tag->inlineTags($this->_doclet);
            }
            if ($tags) {
                foreach ($tags as $aTag) {
                    if ($aTag) {
                        $description .= $aTag->text($this->_doclet);
                    }
                }
            }
            return $this->_doclet->formatter->toFormattedText($description);
		}
        return null;
	}
    
}

?>
