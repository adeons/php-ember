<?php
namespace PhpEmber\Yii\Tests;

/**
 * Contains utility functions to create and free a minimal Yii application
 * instance with database support for ActiveRecord testing.
 */
final class TestHelper {
	
	private static $includeValid;
	private static $includeFile;
	
	private static $activeClasses = array();
	
	/**
	 * Checks if it is possible to create the Yii application singleton.
	 * @return boolean
	 */
	static function canTest() {
		
		if(self::$includeValid === null) {
			self::$includeValid = self::testIncludeFile(self::$includeFile);
		}
		
		return self::$includeValid;
	}
	
	static function testIncludeFile($fileName) {
		
		if(!$fileName || !is_file($fileName)) {
			return false;
		}
		
		require_once($fileName);
		
		return class_exists('Yii');
	}
	
	static function getIncludeFile() {
		return self::$includeFile;
	}
	
	/**
	 * Sets the path of the main Yii include file.
	 * @param string $fileName File name of main Yii file (can be either yii.php or yiilite.php)
	 */
	static function setIncludeFile($fileName) {
		self::$includeValid = null;
		self::$includeFile = $fileName;
	}
	
	/**
	 * Creates the Yii application singleton and sets it up.
	 * @param array $activeClasses List of ActiveRecord class names to be tested.
	 */
	static function setUp(array $activeClasses) {
		
		self::$activeClasses = $activeClasses;
		
		// allow Yii to autoload its classes
		spl_autoload_register(array('YiiBase', 'autoload'));
		
		$app = \Yii::createConsoleApplication(__DIR__ . '/config.php');
		
		$db = $app->getDb();
		
		foreach($activeClasses as $activeClass) {
			
			require_once __DIR__ . '/' . $activeClass . '.php';
			$activeClass::createTable($db);
		}
	}
	
	/**
	 * Relases the application singleton and static references.
	 */
	static function tearDown() {
		
		// unset static ActiveRecord meta data
		foreach(self::$activeClasses as $activeClass) {
			\CActiveRecord::model($activeClass)->refreshMetaData();
		}
		
		// unset connection reference
		\CActiveRecord::$db = null;
		
		// terminate current application instance and unset it
		\Yii::app()->end(0, false);
		\Yii::setApplication(null);
		
		// unregister Yii autoloader
		spl_autoload_unregister(array('YiiBase','autoload'));
	}
	
	/**
	 * Loads test data in ActiveRecord tables.
	 */
	static function fillTables() {
		
		foreach(self::$activeClasses as $activeClass) {
			$activeClass::fillTable(\Yii::app()->getDb());
		}
	}
	
	/**
	 * Clears all ActiveRecord tables.
	 */
	static function clearTables() {
		
		// truncate all tables
		foreach(self::$activeClasses as $activeClass) {
			
			$finder = \CActiveRecord::model($activeClass);
			
			$finder->getDbConnection()->createCommand()->truncateTable(
				$finder->getTableSchema()->name);
		}
	}
	
}
