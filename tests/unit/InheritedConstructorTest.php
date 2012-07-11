<?php
namespace phpdoc\test;

use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/test-common.php';

/**
 * This class tests that inherited constructors are handled properly.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class InheritedConstructorTest extends TestCase {

  public function testInheritedConstructor() {
    $phpdoc = new \PhpDoctor(PHPDOCTOR_DEFAULT_CONFIG);

  }

}
