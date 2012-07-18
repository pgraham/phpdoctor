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

/** This generates the HTML API documentation for each individual interface
 * and class.
 *
 * @package PHPDoctor\Doclets\Zeptech
 */
class ClassWriter extends HTMLWriter {

	/** Build the class definitons.
	 *
	 * @param Doclet doclet
	 */
	public function __construct($doclet) {
		parent::__construct($doclet);
		
		$this->_id = 'definition';

		$rootDoc = $this->_doclet->rootDoc();
		$phpdoctor = $this->_doclet->phpdoctor();
		
		$packages = $rootDoc->packages();
    ksort($packages);

		foreach ($packages as $packageName => $package) {
      $this->_sections = array();

			$this->_sections[] = array('title' => 'Overview', 'url' => 'index.html');
			$this->_sections[] = array(
        'title' => 'Namespace', 
        'url' => $package->asPath().'/package-summary.html'
      );
			$this->_sections[] = array('title' => 'Class', 'selected' => true);
			if ($phpdoctor->getOption('tree')) {
        $this->_sections[] = array(
          'title' => 'Tree',
          'url' => $package->asPath().'/package-tree.html'
        );
      }
			if ($doclet->includeSource()) {
        $this->_sections[] = array(
          'title' => 'Files',
          'url' => 'overview-files.html'
        );
      }
			$this->_sections[] = array(
        'title' => 'Deprecated',
        'url' => 'deprecated-list.html'
      );
			$this->_sections[] = array('title' => 'Todo', 'url' => 'todo-list.html');
			$this->_sections[] = array('title' => 'Index', 'url' => 'index-all.html');
			
			$this->_depth = $package->depth() + 1;
      $rootPath = str_repeat('../', $this->_depth);
			
			$classes = $package->allClasses();
			if (!$classes) {
        return;
      }

      ksort($classes);
      foreach ($classes as $name => $class) {
        $this->_subsections = array();
        
        ob_start();
        
        echo '<div class="qualifiedName">', $class->qualifiedName(), "</div>\n";
        $this->_sourceLocation($class);
        
        echo '<h1>',
             $class->isInterface() ? 'Interface ' : 'Class ',
             $class->name(),
             "</h1>\n\n";
        
        echo '<pre class="tree">';
        $result = $this->_buildTree($rootDoc, $classes[$name]);
        echo $result[0];
        echo "</pre>\n\n";
        
        $implements = $class->interfaces();
        $subclasses = $class->subclasses();
        if (count($implements) > 0 || $subclasses) {
          echo "<dl>\n";
          if (count($implements) > 0) {
            echo "<dt>All Implemented Interfaces:</dt>\n";
            echo '<dd>';
            foreach ($implements as $interfaceName) {
              $interface = $rootDoc->classNamed($interfaceName);
              if ($interface) {
                echo '<a href=', $rootPath, $interface->asPath(), '>';
                if ($interface->packageName() !== $class->packageName()) {
                  echo $interface->packageName(), '\\';
                }
                echo $interface->name(), '</a> ';
              } else {
                echo $interfaceName;
              }
            }
            echo "</dd>\n";
          }
        
          if ($subclasses) {
            echo "<dt>All Known Subclasses:</dt>\n";
            echo '<dd>';
            foreach ($subclasses as $subclass) {
              echo '<a href=', $rootPath, $subclass->asPath(), '>';
              if ($subclass->packageName() != $class->packageName()) {
                echo $subclass->packageName(), '\\';
              }
              echo $subclass->name(), '</a> ';
            }
            echo "</dd>\n";
          }

          echo "</dl>\n\n";
        }
        
        echo $this->_classBlock($class, $rootPath);

        $constants = $class->constants();
        ksort($constants);
        $fields = $class->fields();
        ksort($fields);
        $constructor = $class->constructor();
        $methods = $class->methods(true);
        ksort($methods);

        if ($constants) {
          $this->_subsections['const'] = array(
            'summary' => 'summary_const',
            'detail' => 'detail_const'
          );

          echo '<table id=summary_const>';
          echo '<thead>';
          echo '<tr><th colspan=2>Constant Summary';
          echo '<tbody>';
          foreach ($constants as $field) {
            $textTag = $field->tags('@text');
            echo '<tr>';
            echo '<td class=type><code class=signature>';
            echo $this->_constantModifiers($field);
            echo '</code>';
            echo '<td class=description>';
            echo '<p class=name><a href=#', $field->name(), '>';
            if (is_null($field->constantValue())) echo '$';
            echo $field->name(), '</a></p>';
            if ($textTag) {
              echo '<p class="description">', strip_tags($this->_processInlineTags($textTag, true), '<a><b><strong><u><em>'), '</p>';
            }
          }
          echo '</table>';
        }
        
        if ($fields) {
          $this->_subsections['field'] = array(
            'summary' => 'summary_field',
            'detail' => 'detail_field'
          );

          echo '<table id=summary_field>';
          echo '<thead>';
          echo '<tr><th colspan=2>Field Summary</th></tr>';
          echo '<tbody>';
          foreach ($fields as $field) {
            $textTag = $field->tags('@text');
            echo '<tr>';
            echo '<td class=type><code class=signature>';
            echo $this->_fieldModifiers($field);
            echo '</code>';
            echo '<td class=description>';
            echo '<p class=name><a href=#', $field->name(), '>';
            if (is_null($field->constantValue())) echo '$';
            echo $field->name(), '</a></p>';
            if ($textTag) {
              echo '<p class="description">', strip_tags($this->_processInlineTags($textTag, true), '<a><b><strong><u><em>'), '</p>';
            }
          }
          echo '</table>';
        }
        
        if ($class->superclass()) {
          $superclass = $rootDoc->classNamed($class->superclass());
          if ($superclass) {
            $this->inheritFields($superclass, $rootDoc, $package);
          }
        }
        
        if ($constructor) {
          $this->_subsections['constr'] = array(
            'summary' => 'summary_constructor',
            'detail' => 'detail_constructor'
          );

          echo '<table id=summary_constructor>';
          echo '<thead>';
          echo '<tr><th colspan=2>Constructor Summary';
          $textTag = $constructor->tags('@text');
          echo '<tbody>';
          echo '<tr>';
          echo '<td class=type>';
          echo '<code class=signature>';
          echo $this->_methodModifiers($constructor);
          echo '</code>';
          echo '<td class=description>';
          echo '<p class=name><a href=#', $constructor->name(), '()>', $constructor->name(), '</a>', $constructor->flatSignature(), '</p>';
          if ($textTag) {
            echo '<p class=description>', strip_tags($this->_processInlineTags($textTag, true), '<a><b><strong><u><em>'), '</p>';
          }
          echo '</table>';
        }
        
        if ($methods) {
          $this->_subsections['method'] = array(
            'summary' => 'summary_method',
            'detail' => 'detail_method'
          );

          echo '<table id=summary_method>';
          echo '<tr><th colspan=2>Method Summary</th></tr>';
          foreach($methods as $method) {
            $textTag = $method->tags('@text');
            echo '<tr>';
            echo '<td class=type>';
            echo '<code class=signature>';
            echo $this->_methodModifiers($method);
            echo '</code>';
            echo '<td class=description>';
            echo '<p class=name><a href=#', $method->name(), '()>', $method->name(), '</a>', $method->flatSignature(), '</p>';
            if ($textTag) {
              echo '<p class=description>', strip_tags($this->_processInlineTags($textTag, true), '<a><b><strong><u><em>'), '</p>';
            }
          }
          echo '</table>';
        }
        
        if ($class->superclass()) {
          $superclass = $rootDoc->classNamed($class->superclass());
          if ($superclass) {
            $this->inheritMethods($superclass, $rootDoc, $package);
          }
        }

        if ($constants) {
          echo '<h2 id=detail_const>Constant Detail</h2><div>';
          foreach($constants as $field) {
            echo $this->_constantBlock($field);
          }
          echo '</div>';
        }

        if ($fields) {
          echo '<h2 id="detail_field">Field Detail</h2><div>';
          foreach($fields as $field) {
            echo $this->_fieldBlock($field);
          }
          echo '</div>';
        }
        
        if ($constructor) {
          echo '<h2 id=detail_constructor>Constructor Detail</h2>', '<div>';
          echo $this->_methodBlock($constructor);
          echo '</div>';
        }
        
        if ($methods) {
          echo '<h2 id=detail_method>Method Detail</h2>', '<div>';
          foreach($methods as $method) {
            echo $this->_methodBlock($method);
          }
          echo '</div>';
        }

        $this->_output = ob_get_contents();
        ob_end_clean();
        
        $this->write(strtolower($class->name()).'.html', $class->name(), $package);
      }
		}
  }

	/** Build the class hierarchy tree which is placed at the top of the page.
	 *
	 * @param RootDoc rootDoc The root doc
	 * @param ClassDoc class Class to generate tree for
	 * @param int depth Depth of recursion
	 * @return mixed[]
	 */
	function _buildTree($rootDoc, $class, $depth = null) {
		if ($depth === null) {
			$start = true;
			$depth = 0;
		} else {
			$start = false;
		}
		$output = '';
		$undefinedClass = false;
		if ($class->superclass()) {
			$superclass = $rootDoc->classNamed($class->superclass());
			if ($superclass) {
				$result = $this->_buildTree($rootDoc, $superclass, $depth);
				$output .= $result[0];
				$depth = ++$result[1];
			} else {
				$output .= $class->superclass().'<br>';
				$output .= str_repeat('   ', $depth).' └─';
				$depth++;
				$undefinedClass = true;
			}
		}
		if ($depth > 0 && !$undefinedClass) {
			$output .= str_repeat('   ', $depth).' └─';
		}
		if ($start) {
			$output .= '<strong>'.$class->name().'</strong><br />';
		} else {
			$output .= '<a href="'.str_repeat('../', $this->_depth).$class->asPath().'">'.$class->name().'</a><br>';
		}
		return array($output, $depth);
	}
	
	/** Display the inherited fields of an element. This method calls itself
	 * recursively if the element has a parent class.
	 *
	 * @param ProgramElementDoc element
	 * @param RootDoc rootDoc
	 * @param PackageDoc package
	 */
	function inheritFields($element, $rootDoc, $package) {
		$fields = $element->fields();
		if ($fields) {
      ksort($fields);
			$num = count($fields); $foo = 0;
			echo '<table class="inherit">', "\n";
			echo '<tr><th colspan="2">Fields inherited from ', $element->qualifiedName(), "</th></tr>\n";
			echo '<tr><td>';
			foreach($fields as $field) {
				echo '<a href="', str_repeat('../', $this->_depth), $field->asPath(), '">', $field->name(), '</a>';
				if (++$foo < $num) {
					echo ', ';
				}
			}
			echo '</td></tr>';
			echo "</table>\n\n";
			if ($element->superclass()) {
        $superclass = $rootDoc->classNamed($element->superclass());
        if ($superclass) {
          $this->inheritFields($superclass, $rootDoc, $package);
        }
			}
		}
	}
	
	/** Display the inherited methods of an element. This method calls itself
	 * recursively if the element has a parent class.
	 *
	 * @param ProgramElementDoc element
	 * @param RootDoc rootDoc
	 * @param PackageDoc package
	 */
	function inheritMethods($element, $rootDoc, $package) {
		$methods = $element->methods();
		if ($methods) {
      ksort($methods);
			$num = count($methods); $foo = 0;
			echo '<table class="inherit">', "\n";
			echo '<tr><th colspan="2">Methods inherited from ', $element->qualifiedName(), "</th></tr>\n";
			echo '<tr><td>';
			foreach($methods as $method) {
				echo '<a href="', str_repeat('../', $this->_depth), $method->asPath(), '">', $method->name(), '</a>';
				if (++$foo < $num) {
					echo ', ';
				}
			}
			echo '</td></tr>';
			echo "</table>\n\n";
			if ($element->superclass()) {
        $superclass = $rootDoc->classNamed($element->superclass());
        if ($superclass) {
          $this->inheritMethods($superclass, $rootDoc, $package);
        }
			}
		}
	}

  private function _classBlock(ClassDoc $elm, $rootPath) {
    $textTag = $elm->tags('@text');

    ob_start();

    echo '<div class=class-detail>';
    echo $this->_classSignature($elm, $rootPath);
    if ($textTag) {
      echo '<div class=comment id=overview_description>',
           $this->_processInlineTags($textTag),
           "</div>\n\n";
    }

    $this->_processTags($elm->tags());
    echo '</div>';

    return ob_get_clean();
  }

  private function _classSignature(ClassDoc $elm, $rootPath) {
    $access = $elm->access();

    $sig = '<p class=signature>';
    $sig .= "<span class=$access>$access</span> ";

    if ($elm->isAbstract()) {
      $sig .= '<span class=abstract>abstract</span> ';
    }

    $sig .= $elm->isInterface() ? 'interface ' : 'class ';
    $sig .= "<strong>{$elm->name()}</strong>";

    if ($elm->superclass()) {
      $rootDoc = $this->_doclet->rootDoc();
      $superclass = $rootDoc->classNamed($elm->superclass());

      if ($superclass) {
        $superclassPath = $rootPath . $superclass->asPath();
        $sig .= " extends <a href=$superclassPath>{$superclass->name()}</a>";
      } else {
        $sig .= " extends {$elm->superclass()}";
      }
    }

    
    $sig .= '</p>';
    return $sig;
  }

  private function _constantBlock(FieldDoc $elm) {
    $textTag = $elm->tags('@text');
    $type = $elm->type();

    ob_start();

    echo '<div class=constant-detail>';
    $this->_sourceLocation($elm);
    echo '<h3 id=', $elm->name(),'>', $elm->name(), '</h3>';
    echo $this->_constantSignature($elm);
    echo '<div class=details>';
    if ($textTag) {
      echo $this->_processInlineTags($textTag);
    }
    $this->_processTags($elm->tags());
    echo '</div></div>';

    return ob_get_clean();
  }

  private function _constantModifiers(FieldDoc $elm) {
    ob_start();

    echo '<span class=constant>constant</span> ',
         '<span class=type>', $elm->typeAsString(), '</span>';

    return ob_get_clean();
  }

  private function _constantSignature(FieldDoc $elm) {
    ob_start();

    echo '<code class=signature>';
    echo $this->_constantModifiers($elm);
    echo ' <strong>', $elm->name(), '</strong>';
    if (!is_null($elm->value())) {
      echo ' = ', htmlspecialchars($elm->value());
    }
    echo '</code>';

    return ob_get_clean();
  }

  private function _fieldBlock(FieldDoc $elm) {
    $textTag = $elm->tags('@text');
    $type = $elm->type();

    ob_start();

    echo '<div class=field-detail>';
    $this->_sourceLocation($elm);
    echo '<h3 id=', $elm->name(),'>', $elm->name(), '</h3>';
    echo $this->_fieldSignature($elm);
    echo '<div class="details">', "\n";
    if ($textTag) {
      echo $this->_processInlineTags($textTag);
    }
    $this->_processTags($elm->tags());
    echo '</div></div>';

    return ob_get_clean();
  }

  private function _fieldModifiers(FieldDoc $elm) {
    $access = $elm->access();

    ob_start();

    echo '<span class=', $access, '>', $access, '</span> ';
    if ($elm->isStatic()) {
      echo '<span class=static>static</span> ';
    }

    if ($elm->isFinal()) {
      echo '<span class=final>final</span> ';
    }

    echo '<span class=type', $elm->typeAsString(), '</span>';

    return ob_get_clean();
  }

  private function _fieldSignature(FieldDoc $elm) {
    ob_start();

    echo '<code class=signature>';
    echo $this->_fieldModifiers($elm);
    echo ' <strong>$', $elm->name(), '</strong>';
    if (!is_null($elm->value())) {
      echo ' = ', htmlspecialchars($elm->value());
    }
    echo "</code>\n";

    return ob_get_clean();
  }

  private function _methodBlock(MethodDoc $elm) {
    $textTag = $elm->tags('@text');

    ob_start();

    echo '<div class=method-detail>';
    $this->_sourceLocation($elm);
    echo '<h3 id=', $elm->name(), '()>', $elm->name(), '</h3>';
    echo $this->_methodSignature($elm);
    echo '<div class=details>';
    if ($textTag) {
      echo $this->_processInlineTags($textTag);
    }
    $this->_processTags($elm->tags());
    echo '</div></div>';

    return ob_get_clean();
  }

  private function _methodModifiers(MethodDoc $elm) {
    $access = $elm->access();

    ob_start();

    echo '<span class=', $access, '>', $access, '</span> ';

    if ($elm->isAbstract()) {
      echo '<span class=abstract>abstract</span> ';
    }

    if ($elm->isStatic()) {
      echo '<span class=static>static</span> ';
    }

    if ($elm->isFinal()) {
      echo '<span class=final>final</span> ';
    }

    echo '<span class=type>', $elm->returnTypeAsString(), '</span>';

    return ob_get_clean();
  }

  private function _methodSignature(MethodDoc $elm) {
    ob_start();

    echo '<code class=signature>';
    echo $this->_methodModifiers($elm);
    echo ' <strong>', $elm->name(), '</strong> ';
    echo $elm->flatSignature();
    echo '</code>';

    return ob_get_clean();
  }

}
