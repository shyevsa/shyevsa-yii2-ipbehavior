<?php

namespace shyevsa\ipbehavior\tests;

use Yii;
use yii\db\Schema;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use yii\web\Application;

/**
 * This is the base class for all yii framework unit tests.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Clean up after test.
     * By default, the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->destroyDb();
        $this->destroyApplication();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
        $this->migrateDb();
    }

    /**
     * @param array $config
     * @param string $appClass
     * @return Application
     */
    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'aliases' => [
                '@bower' => '@vendor/bower-asset',
                '@npm' => '@vendor/npm-asset',
            ],
            'components' => [
                'request' => [
                    'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite:' . dirname(__DIR__) . '/tests/data/testdb.sqlite',
                ],
            ],
        ], $config));
    }

    protected function migrateDb()
    {
        $db = Yii::$app->getDb();
        $db->createCommand()->createTable('post', [
            'id' => Schema::TYPE_PK,
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'created_from' => Schema::TYPE_BINARY,
            'updated_from' => Schema::TYPE_BINARY,
            'created_ip' => Schema::TYPE_STRING,
            'updated_ip' => Schema::TYPE_STRING,
        ])->execute();
    }

    protected function destroyDb()
    {
        $db = Yii::$app->getDb();
        $db->createCommand()->dropTable('post')->execute();
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }
}
