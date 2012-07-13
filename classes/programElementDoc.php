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

/** Represents a PHP program element: global, function, class, interface,
 * field, constructor, or method. This is an abstract class dealing with
 * information common to these elements.
 *
 * @package PHPDoctor
 * @abstract
 */
abstract class ProgramElementDoc extends Doc {

  /** Reference to the elements parent.
   *
   * @var doc
   */
  protected $_parent = null;

  /** The elements package.
   *
   * @var str
   */
  protected $_package = null;

  /** If this element is final.
   *
   * @var bool
   */
  protected $_final = false;

  /** Access type for this element.
   *
   * @var str
   */
  protected $_access = 'public';

  /** If this element is static.
   *
   * @var bool
   */
  protected $_static = false;
  
  /** Which source file is this element in
   *
   * @var str
   */
  protected $_filename = null;
  
  /** The line in the source file this element can be found at
   *
   * @var int
   */
  protected $_lineNumber = null;
  
  /** The source path containing the source file
   *
   * @var str
   */
  protected $_sourcePath = null;

  public function __construct($name, $parent, $root, $filename, $lineNumber,
      $sourcePath)
  {
    parent::__construct($name, $root);
    $this->_parent = $parent;
    $this->_filename = $filename;
    $this->_lineNumber = $lineNumber;
    $this->_sourcePath = $sourcePath;
  }
  
  /** Set element to have public access */
  public function makePublic() {
    $this->_access = 'public';
  }

  /** Set element to have protected access */
  public function makeProtected() {
    $this->_access = 'protected';
  }

  /** Set element to have private access */
  public function makePrivate() {
    $this->_access = 'private';
  }

  /** Get the containing class of this program element. If the element is in
   * the global scope and does not have a parent class, this will return null.
   *
   * @return ClassDoc
   */
  function containingClass() {
        $return = null;
        if (strtolower(get_class($this->_parent)) == 'classdoc') {
            $return = $this->_parent;
        }
        return $return;
  }

  /** Get the package that this program element is contained in.
   *
   * @return PackageDoc
   */
  function containingPackage() {
    return $this->_root->packageNamed($this->_package);
  }
  
  /** Get the name of the package that this program element is contained in.
   *
   * @return str
   */
  function packageName() {
    return $this->_package;
  }

  /** Get the fully qualified name.
   *
   * <pre>Example:
for the method bar() in class Foo in the package Baz, return:
  Baz\Foo\bar()</pre>
   *
   * @return str
   */
  function qualifiedName() {
    $parent = $this->containingClass();
    $parentName = '';
    if ($parent) {
      $parentName = $parent->name();
    }

    if ($parentName && $this->_package !== $parentName) {
      return $this->_package.'\\'.$parent->name().'\\'.$this->_name;
    } else {
      return $this->_package.'\\'.$this->_name;
    }
  }

  /** Get modifiers string.
   *
   * <pre> Example, for:
  public abstract int foo() { ... }
modifiers() would return:
  'public abstract'</pre>
   *
   * @return str
   */
  public function modifiers($showPublic = true) {
    $modifiers = '';
    if ($showPublic || $this->_access != 'public') {
      $modifiers .= $this->_access.' ';
    }
    if ($this->_final) {
      $modifiers .= 'final ';
    }
    if (isset($this->_abstract) && $this->_abstract) {
      $modifiers .= 'abstract ';
    }
    if ($this->_static) {
      $modifiers .= 'static ';
    }
    return $modifiers;
  }

  /** Return true if this program element is public.
   *
   * @return bool
   */ 
  public function isPublic() {
    return $this->_access === 'public';
  }

  /** Return true if this program element is protected.
   *
   * @return bool
   */ 
  public function isProtected() {
    return $this->_access === 'protected';
  }

  /** Return true if this program element is private.
   *
   * @return bool
   */ 
  public function isPrivate() {
    return $this->_access === 'private';
  }
  
  /** Return true if this program element is final.
   *
   * @return bool
   */ 
  function isFinal() {
    return $this->_final;
  }
  
  /** Return true if this program element is static.
   *
   * @return bool
   */ 
  function isStatic() {
    return $this->_static;
  }
  
  /** Get the source location of this element
   *
   * @return str
   */
  function location() {
    return $this->sourceFilename().' at line '.$this->sourceLine();
  }
  
  function sourceFilename() {
    $phpdoctor = $this->_root->phpdoctor();
      return substr($this->_filename, strlen($this->_sourcePath) + 1);
  }
  
  function sourceLine() {
      return $this->_lineNumber;
  }
    
  /** Return the element path.
   *
   * @return str
   */
  function asPath() {
    if ($this->isClass() || $this->isInterface() || $this->isException()) {
      return strtolower(str_replace('.', '/', str_replace('\\', '/', $this->_package)).'/'.$this->_name.'.html');
    } elseif ($this->isField()) {
      $class = $this->containingClass();
      if ($class) {
        return strtolower(str_replace('.', '/', str_replace('\\', '/', $this->_package)).'/'.$class->name().'.html#').$this->_name;
      } else {
        return strtolower(str_replace('.', '/', str_replace('\\', '/', $this->_package)).'/package-globals.html#').$this->_name;
      }
    } elseif ($this->isConstructor() || $this->isMethod()) {
      $class = $this->containingClass();
      if ($class) {
        return strtolower(str_replace('.', '/', str_replace('\\', '/', $this->_package)).'/'.$class->name().'.html#').$this->_name.'()';
      } else {
        return strtolower(str_replace('.', '/', str_replace('\\', '/', $this->_package)).'/package-functions.html#').$this->_name.'()';
      }
    } elseif ($this->isGlobal()) {
      return strtolower(str_replace('.', '/', str_replace('\\', '/', $this->_package)).'/package-globals.html#').$this->_name;
    } elseif ($this->isFunction()) {
      return strtolower(str_replace('.', '/', str_replace('\\', '/', $this->_package)).'/package-functions.html#').$this->_name.'()';
    }
    return null;
  }

}

?>
