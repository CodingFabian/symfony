<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Components\DependencyInjection;

require_once __DIR__.'/../../bootstrap.php';

use Symfony\Components\DependencyInjection\Builder;
use Symfony\Components\DependencyInjection\BuilderConfiguration;
use Symfony\Components\DependencyInjection\Definition;
use Symfony\Components\DependencyInjection\Reference;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
  static protected $fixturesPath;

  static public function setUpBeforeClass()
  {
    self::$fixturesPath = __DIR__.'/../../../../fixtures/Symfony/Components/DependencyInjection/';
  }

  public function testDefinitions()
  {
    $builder = new Builder();
    $definitions = array(
      'foo' => new Definition('FooClass'),
      'bar' => new Definition('BarClass'),
    );
    $builder->setDefinitions($definitions);
    $this->assertEquals($builder->getDefinitions(), $definitions, '->setDefinitions() sets the service definitions');
    $this->assertTrue($builder->hasDefinition('foo'), '->hasDefinition() returns true if a service definition exists');
    $this->assertTrue(!$builder->hasDefinition('foobar'), '->hasDefinition() returns false if a service definition does not exist');

    $builder->setDefinition('foobar', $foo = new Definition('FooBarClass'));
    $this->assertEquals($builder->getDefinition('foobar'), $foo, '->getDefinition() returns a service definition if defined');
    $this->assertTrue($builder->setDefinition('foobar', $foo = new Definition('FooBarClass')) === $foo, '->setDefinition() implements a fuild interface by returning the service reference');

    $builder->addDefinitions($defs = array('foobar' => new Definition('FooBarClass')));
    $this->assertEquals($builder->getDefinitions(), array_merge($definitions, $defs), '->addDefinitions() adds the service definitions');

    try
    {
      $builder->getDefinition('baz');
      $this->fail('->getDefinition() throws an InvalidArgumentException if the service definition does not exist');
    }
    catch (\InvalidArgumentException $e)
    {
    }
  }

  public function testRegister()
  {
    $builder = new Builder();
    $builder->register('foo', 'FooClass');
    $this->assertTrue($builder->hasDefinition('foo'), '->register() registers a new service definition');
    $this->assertTrue($builder->getDefinition('foo') instanceof Definition, '->register() returns the newly created Definition instance');
  }

  public function testHasService()
  {
    $builder = new Builder();
    $this->assertTrue(!$builder->hasService('foo'), '->hasService() returns false if the service does not exist');
    $builder->register('foo', 'FooClass');
    $this->assertTrue($builder->hasService('foo'), '->hasService() returns true if a service definition exists');
    $builder->bar = new \stdClass();
    $this->assertTrue($builder->hasService('bar'), '->hasService() returns true if a service exists');
  }

  public function testGetService()
  {
    $builder = new Builder();
    try
    {
      $builder->getService('foo');
      $this->fail('->getService() throws an InvalidArgumentException if the service does not exist');
    }
    catch (\InvalidArgumentException $e)
    {
    }
    $builder->register('foo', 'stdClass');
    $this->assertTrue(is_object($builder->getService('foo')), '->getService() returns the service definition associated with the id');
    $builder->bar = $bar = new \stdClass();
    $this->assertEquals($builder->getService('bar'), $bar, '->getService() returns the service associated with the id');
    $builder->register('bar', 'stdClass');
    $this->assertEquals($builder->getService('bar'), $bar, '->getService() returns the service associated with the id even if a definition has been defined');

    $builder->register('baz', 'stdClass')->setArguments(array(new Reference('baz')));
    try
    {
      @$builder->getService('baz');
      $this->fail('->getService() throws a LogicException if the service has a circular reference to itself');
    }
    catch (\LogicException $e)
    {
    }

    $builder->register('foobar', 'stdClass')->setShared(true);
    $this->assertTrue($builder->getService('bar') === $builder->getService('bar'), '->getService() always returns the same instance if the service is shared');
  }

  public function testGetServiceIds()
  {
    $builder = new Builder();
    $builder->register('foo', 'stdClass');
    $builder->bar = $bar = new \stdClass();
    $builder->register('bar', 'stdClass');
    $this->assertEquals($builder->getServiceIds(), array('foo', 'bar', 'service_container'), '->getServiceIds() returns all defined service ids');
  }

  public function testAliases()
  {
    $builder = new Builder();
    $builder->register('foo', 'stdClass');
    $builder->setAlias('bar', 'foo');
    $this->assertTrue($builder->hasAlias('bar'), '->hasAlias() returns true if the alias exists');
    $this->assertTrue(!$builder->hasAlias('foobar'), '->hasAlias() returns false if the alias does not exist');
    $this->assertEquals($builder->getAlias('bar'), 'foo', '->getAlias() returns the aliased service');
    $this->assertTrue($builder->hasService('bar'), '->setAlias() defines a new service');
    $this->assertTrue($builder->getService('bar') === $builder->getService('foo'), '->setAlias() creates a service that is an alias to another one');

    try
    {
      $builder->getAlias('foobar');
      $this->fail('->getAlias() throws an InvalidArgumentException if the alias does not exist');
    }
    catch (\InvalidArgumentException $e)
    {
    }
  }

  public function testGetAliases()
  {
    $builder = new Builder();
    $builder->setAlias('bar', 'foo');
    $builder->setAlias('foobar', 'foo');
    $this->assertEquals($builder->getAliases(), array('bar' => 'foo', 'foobar' => 'foo'), '->getAliases() returns all service aliases');
    $builder->register('bar', 'stdClass');
    $this->assertEquals($builder->getAliases(), array('foobar' => 'foo'), '->getAliases() does not return aliased services that have been overridden');
    $builder->setService('foobar', 'stdClass');
    $this->assertEquals($builder->getAliases(), array(), '->getAliases() does not return aliased services that have been overridden');
  }

  public function testCreateService()
  {
    $builder = new Builder();
    $builder->register('foo1', 'FooClass')->setFile(self::$fixturesPath.'/includes/foo.php');
    $this->assertTrue($builder->getService('foo1') instanceof \FooClass, '->createService() requires the file defined by the service definition');
    $builder->register('foo2', 'FooClass')->setFile(self::$fixturesPath.'/includes/%file%.php');
    $builder->setParameter('file', 'foo');
    $this->assertTrue($builder->getService('foo2') instanceof \FooClass, '->createService() replaces parameters in the file provided by the service definition');
  }

  public function testCreateServiceClass()
  {
    $builder = new Builder();
    $builder->register('foo1', '%class%');
    $builder->setParameter('class', 'stdClass');
    $this->assertTrue($builder->getService('foo1') instanceof \stdClass, '->createService() replaces parameters in the class provided by the service definition');
  }

  public function testCreateServiceArguments()
  {
    $builder = new Builder();
    $builder->register('bar', 'stdClass');
    $builder->register('foo1', 'FooClass')->addArgument(array('foo' => '%value%', '%value%' => 'foo', new Reference('bar')));
    $builder->setParameter('value', 'bar');
    $this->assertEquals($builder->getService('foo1')->arguments, array('foo' => 'bar', 'bar' => 'foo', $builder->getService('bar')), '->createService() replaces parameters and service references in the arguments provided by the service definition');
  }

  public function testCreateServiceConstructor()
  {
    $builder = new Builder();
    $builder->register('bar', 'stdClass');
    $builder->register('foo1', 'FooClass')->setConstructor('getInstance')->addArgument(array('foo' => '%value%', '%value%' => 'foo', new Reference('bar')));
    $builder->setParameter('value', 'bar');
    $this->assertTrue($builder->getService('foo1')->called, '->createService() calls the constructor to create the service instance');
    $this->assertEquals($builder->getService('foo1')->arguments, array('foo' => 'bar', 'bar' => 'foo', $builder->getService('bar')), '->createService() passes the arguments to the constructor');
  }

  public function testCreateServiceMethodCalls()
  {
    $builder = new Builder();
    $builder->register('bar', 'stdClass');
    $builder->register('foo1', 'FooClass')->addMethodCall('setBar', array(array('%value%', new Reference('bar'))));
    $builder->setParameter('value', 'bar');
    $this->assertEquals($builder->getService('foo1')->bar, array('bar', $builder->getService('bar')), '->createService() replaces the values in the method calls arguments');
  }

  public function testCreateServiceConfigurator()
  {
    require_once self::$fixturesPath.'/includes/classes.php';

    $builder = new Builder();
    $builder->register('foo1', 'FooClass')->setConfigurator('sc_configure');
    $this->assertTrue($builder->getService('foo1')->configured, '->createService() calls the configurator');

    $builder->register('foo2', 'FooClass')->setConfigurator(array('%class%', 'configureStatic'));
    $builder->setParameter('class', 'BazClass');
    $this->assertTrue($builder->getService('foo2')->configured, '->createService() calls the configurator');

    $builder->register('baz', 'BazClass');
    $builder->register('foo3', 'FooClass')->setConfigurator(array(new Reference('baz'), 'configure'));
    $this->assertTrue($builder->getService('foo3')->configured, '->createService() calls the configurator');

    $builder->register('foo4', 'FooClass')->setConfigurator('foo');
    try
    {
      $builder->getService('foo4');
      $this->fail('->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
    }
    catch (\InvalidArgumentException $e)
    {
    }
  }

  public function testResolveValue()
  {
    $this->assertEquals(Builder::resolveValue('foo', array()), 'foo', '->resolveValue() returns its argument unmodified if no placeholders are found');
    $this->assertEquals(Builder::resolveValue('I\'m a %foo%', array('foo' => 'bar')), 'I\'m a bar', '->resolveValue() replaces placeholders by their values');
    $this->assertTrue(Builder::resolveValue('%foo%', array('foo' => true)) === true, '->resolveValue() replaces arguments that are just a placeholder by their value without casting them to strings');

    $this->assertEquals(Builder::resolveValue(array('%foo%' => '%foo%'), array('foo' => 'bar')), array('bar' => 'bar'), '->resolveValue() replaces placeholders in keys and values of arrays');

    $this->assertEquals(Builder::resolveValue(array('%foo%' => array('%foo%' => array('%foo%' => '%foo%'))), array('foo' => 'bar')), array('bar' => array('bar' => array('bar' => 'bar'))), '->resolveValue() replaces placeholders in nested arrays');

    $this->assertEquals(Builder::resolveValue('I\'m a %%foo%%', array('foo' => 'bar')), 'I\'m a %foo%', '->resolveValue() supports % escaping by doubling it');
    $this->assertEquals(Builder::resolveValue('I\'m a %foo% %%foo %foo%', array('foo' => 'bar')), 'I\'m a bar %foo bar', '->resolveValue() supports % escaping by doubling it');

    try
    {
      Builder::resolveValue('%foobar%', array());
      $this->fail('->resolveValue() throws a RuntimeException if a placeholder references a non-existant parameter');
    }
    catch (\RuntimeException $e)
    {
    }

    try
    {
      Builder::resolveValue('foo %foobar% bar', array());
      $this->fail('->resolveValue() throws a RuntimeException if a placeholder references a non-existant parameter');
    }
    catch (\RuntimeException $e)
    {
    }
  }

  public function testResolveServices()
  {
    $builder = new Builder();
    $builder->register('foo', 'FooClass');
    $this->assertEquals($builder->resolveServices(new Reference('foo')), $builder->getService('foo'), '->resolveServices() resolves service references to service instances');
    $this->assertEquals($builder->resolveServices(array('foo' => array('foo', new Reference('foo')))), array('foo' => array('foo', $builder->getService('foo'))), '->resolveServices() resolves service references to service instances in nested arrays');
  }

  public function testMerge()
  {
    $container = new Builder();
    $container->merge(null);
    $this->assertEquals($container->getParameters(), array(), '->merge() accepts null as an argument');
    $this->assertEquals($container->getDefinitions(), array(), '->merge() accepts null as an argument');

    $container = new Builder(array('bar' => 'foo'));
    $config = new BuilderConfiguration();
    $config->setParameters(array('foo' => 'bar'));
    $container->merge($config);
    $this->assertEquals($container->getParameters(), array('bar' => 'foo', 'foo' => 'bar'), '->merge() merges current parameters with the loaded ones');

    $container = new Builder(array('bar' => 'foo', 'foo' => 'baz'));
    $config = new BuilderConfiguration();
    $config->setParameters(array('foo' => 'bar'));
    $container->merge($config);
    $this->assertEquals($container->getParameters(), array('bar' => 'foo', 'foo' => 'baz'), '->merge() does not change the already defined parameters');

    $container = new Builder(array('bar' => 'foo'));
    $config = new BuilderConfiguration();
    $config->setParameters(array('foo' => '%bar%'));
    $container->merge($config);
    $this->assertEquals($container->getParameters(), array('bar' => 'foo', 'foo' => 'foo'), '->merge() evaluates the values of the parameters towards already defined ones');

    $container = new Builder(array('bar' => 'foo'));
    $config = new BuilderConfiguration();
    $config->setParameters(array('foo' => '%bar%', 'baz' => '%foo%'));
    $container->merge($config);
    $this->assertEquals($container->getParameters(), array('bar' => 'foo', 'foo' => 'foo', 'baz' => 'foo'), '->merge() evaluates the values of the parameters towards already defined ones');

    $container = new Builder();
    $container->register('foo', 'FooClass');
    $container->register('bar', 'BarClass');
    $config = new BuilderConfiguration();
    $config->setDefinition('baz', new Definition('BazClass'));
    $config->setAlias('alias_for_foo', 'foo');
    $container->merge($config);
    $this->assertEquals(array_keys($container->getDefinitions()), array('foo', 'bar', 'baz'), '->merge() merges definitions already defined ones');
    $this->assertEquals($container->getAliases(), array('alias_for_foo' => 'foo'), '->merge() registers defined aliases');

    $container = new Builder();
    $container->register('foo', 'FooClass');
    $config->setDefinition('foo', new Definition('BazClass'));
    $container->merge($config);
    $this->assertEquals($container->getDefinition('foo')->getClass(), 'BazClass', '->merge() overrides already defined services');
  }

  public function testFindAnnotatedServiceIds()
  {
    $builder = new Builder();
    $builder
      ->register('foo', 'FooClass')
      ->addAnnotation('foo', array('foo' => 'foo'))
      ->addAnnotation('bar', array('bar' => 'bar'))
      ->addAnnotation('foo', array('foofoo' => 'foofoo'))
    ;
    $this->assertEquals($builder->findAnnotatedServiceIds('foo'), array(
      'foo' => array(
        array('foo' => 'foo'),
        array('foofoo' => 'foofoo'),
      )
    ), '->findAnnotatedServiceIds() returns an array of service ids and its annotation attributes');
    $this->assertEquals($builder->findAnnotatedServiceIds('foobar'), array(), '->findAnnotatedServiceIds() returns an empty array if there is annotated services');
  }
}
