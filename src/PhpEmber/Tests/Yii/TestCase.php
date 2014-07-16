<?php
namespace PhpEmber\Tests\Yii;

/**
 * Ensures Yii is included before executing tests.
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        if (!defined('YII_INCLUDE_FILE') || !is_file(YII_INCLUDE_FILE)) {
            $this->markTestSkipped('YII_INCLUDE_FILE not defined or invalid.');
            return;
        }

        require_once YII_INCLUDE_FILE;

        spl_autoload_register(array('YiiBase','autoload'));
    }

    protected function tearDown()
    {
        // unregister autoloader (causes conflicts)
        spl_autoload_unregister(array('YiiBase','autoload'));
    }

}
