<?php
namespace phpdoc\test;

use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/test-common.php';

/**
 * This class tests that constants are parsed correctly.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ConstantsTest extends TestCase {

  const TEST_NS = 'phpdoc\\test\\constantsTest';

  private static $_rootDoc;

  public static function setUpBeforeClass() {
    self::$_rootDoc = parseSource('constantsTest');
  }

  public function testClassConstant() {
    $classes = self::$_rootDoc->classes();

    $classKey = 'ClassWithConstants.' . self::TEST_NS;
    $this->assertArrayHasKey($classKey, $classes);
    $class = $classes[$classKey];
    $this->assertInstanceOf('ClassDoc', $class);

    $constants = $class->constants();
    $this->assertCount(1, $constants);

    $constant = $constants['MY_CONST'];
    $this->assertEquals('MY_CONST', $constant->name());
    $this->assertEquals("'constant_value'", $constant->value());
  }

  public function testConstantOutsideOfClass() {
    $packages = self::$_rootDoc->packages();
    $classes = self::$_rootDoc->classes();

    $package = $packages[self::TEST_NS];
    $globals = $package->globals();

    $this->assertArrayHasKey('PACKAGE_CONST', $globals,
      print_r(array_keys($globals), true));
    $constant = $globals['PACKAGE_CONST'];
    $this->assertEquals('PACKAGE_CONST', $constant->name());
    $this->assertEquals("'constant_value'", $constant->value());

    $classKey = 'ConstantsOutsideOfClass.' . self::TEST_NS;
    $this->assertArrayHasKey($classKey, $classes);
    $class = $classes[$classKey];
    $this->assertInstanceOf('ClassDoc', $class);

    $constants = $class->constants();
    $this->assertArrayHasKey('MY_CONST', $constants,
      print_r(array_keys($constants), true));

    $constant = $constants['MY_CONST'];
    $this->assertEquals('MY_CONST', $constant->name());
    $this->assertEquals("'constant_value'", $constant->value());
  }

}
