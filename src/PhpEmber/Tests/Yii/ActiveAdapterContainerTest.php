<?php
namespace PhpEmber\Tests\Yii;

use PhpEmber\Yii\ActiveAdapterContainer;

class ActiveAdapterContainerTest extends TestCase
{
    
    public function testAddActiveClass() 
    {
        $container = new ActiveAdapterContainer;
        $container->addActiveClass('Fruit');
        
        $this->assertArrayHasKey('fruit', $container->getActiveClasses());
    }
    
    public function testSetActiveClasses()
    {
        $container = new ActiveAdapterContainer;
        $container->setActiveClasses(array('fruit' => 'Fruit'));
    
        $this->assertArrayHasKey('fruit', $container->getActiveClasses());
    }
    
    public function testSetActiveClassesWithoutTypeKey()
    {
        $container = new ActiveAdapterContainer;
        $container->setActiveClasses(array('Fruit'));
    
        $this->assertArrayHasKey('fruit', $container->getActiveClasses());
    }
    
    public function testHas() 
    {
        $container = new ActiveAdapterContainer;
        $container->addActiveClass('Fruit', 'fruit');
        
        $this->assertTrue($container->has('fruit'));
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNotFound()
    {
        $container = new ActiveAdapterContainer;
        $container->get('fruit');
    }
    
    public function testGetCallsCreate()
    {
        $container = $this->getMock(
            '\\PhpEmber\\Yii\\ActiveAdapterContainer', array('create'));
        
        $container->expects($this->once())
            ->method('create')
            ->with($this->equalTo('fruit'), $this->equalTo('Fruit'));
        
        $container->addActiveClass('Fruit', 'fruit');
        $container->get('fruit');
    }
    
    public function testGetWillReuseAdapter()
    {
        $fruits = $this->getMock(
            '\\PhpEmber\\Yii\\ActiveAdapter', array(), array(), '', false);
        
        $container = $this->getMock(
            '\\PhpEmber\\Yii\\ActiveAdapterContainer', array('create'));
        
        $container->expects($this->once())
            ->method('create')
            ->will($this->returnValue($fruits));
        
        $container->addActiveClass('Fruit', 'fruit');
        
        // should call create
        $this->assertSame($fruits, $container->get('fruit'));
        
        // should not call create, but reuse adapter instance
        $this->assertSame($fruits, $container->get('fruit'));
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTypeKeyOfClassNotRegistered()
    {
        $container = new ActiveAdapterContainer;
        $container->typeKeyOfClass('Fruit');
    }
    
    public function testTypeKeyOfClass()
    {
        $container = new ActiveAdapterContainer;
        $container->addActiveClass('Fruit', 'fruit');
        
        $this->assertEquals('fruit', $container->typeKeyOfClass('Fruit'));
    }
    
}
