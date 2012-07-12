<?php
namespace phpdoc\test\inheritedConstructorTest;
class ConcreteClassNoTagsConstructor extends BaseClass {

  /**
   * Concrete class constructor with no parameter tags.
   */
  public function __construct($param1, $param2) { }
}
