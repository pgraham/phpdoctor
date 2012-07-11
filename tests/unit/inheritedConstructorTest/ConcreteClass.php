<?php
namespace phpdoc\test\inheritedConstructorTest;
class ConcreteClass extends BaseClass {

  /**
   * Concrete class constructor.
   *
   * @param string arg1 The first concrete class parameter.
   * @param string arg2 The second concrete class parameter.
   */
  public function __construct($arg1, $arg2) {
  }
}
