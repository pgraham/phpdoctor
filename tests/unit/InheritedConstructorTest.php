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

  public function testInheritedConstructor() {
    $phpdoc = new \PhpDoctor(PHPDOCTOR_DEFAULT_CONFIG);
    $phpdoc->setOption('source_path', './inheritedConstructorTest');
    $phpdoc->setOption('quiet', true);

    $rootDoc = $phpdoc->parse();

    $classes = $rootDoc->classes();
    $this->assertCount(3, $classes);

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
    $phpdoc = new \PhpDoctor(PHPDOCTOR_DEFAULT_CONFIG);
    $phpdoc->setOption('source_path', './inheritedConstructorTest');
    $phpdoc->setOption('quiet', true);

    $rootDoc = $phpdoc->parse();

    $classes = $rootDoc->classes();
    $this->assertCount(3, $classes);

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

}
