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

  const TEST_NS = 'phpdoc\\test\\inheritedConstructorTest';

  private static $_rootDoc;

  public static function setUpBeforeClass() {
    self::$_rootDoc = parseSource('inheritedConstructorTest');
  }

  public function testInheritedConstructor() {
    $classes = self::$_rootDoc->classes();

    $concreteClass = $classes['ConcreteClass.' . self::TEST_NS];
    $this->assertInstanceOf('ClassDoc', $concreteClass);

    $constructor = $concreteClass->constructor();
    $this->assertInstanceOf('MethodDoc', $constructor);
    $this->assertTrue($constructor->isConstructor());
    
    $textTag = $constructor->tags('@text');
    $this->assertEquals('Concrete class constructor.', $textTag->_text);

    $this->assertArrayHasKey('@param', $constructor->_tags);
    $this->assertCount(2, $constructor->_tags['@param']);
  }

  public function testInheritedConstructorNoParams() {
    $classes = self::$_rootDoc->classes();

    $classKey = 'ConcreteClassNoArgsConstructor.' . self::TEST_NS;
    $concreteClass = $classes[$classKey];
    $this->assertInstanceOf('ClassDoc', $concreteClass);

    $constructor = $concreteClass->constructor();
    $this->assertInstanceOf('MethodDoc', $constructor);
    $this->assertTrue($constructor->isConstructor());
    
    $textTag = $constructor->tags('@text');
    $this->assertEquals('Concrete class constructor with no parameters.',
      $textTag->_text);

    $this->assertArrayNotHasKey('@param', $constructor->_tags);
  }

  /*
   * Test that overriding methods inherit superclass param doc for parameters
   * with the same name.
   */
  public function testInheritedTags() {
    $classes = self::$_rootDoc->classes();

    $classKey = 'ConcreteClassNoTagsConstructor.' . self::TEST_NS;
    $concreteClass = $classes[$classKey];
    $this->assertInstanceOf('ClassDoc', $concreteClass);

    $constructor = $concreteClass->constructor();
    $this->assertInstanceOf('MethodDoc', $constructor);
    $this->assertTrue($constructor->isConstructor());

    $this->assertArrayHasKey('@param', $constructor->_tags);
    $this->assertCount(2, $constructor->_tags['@param']);
  }

}
