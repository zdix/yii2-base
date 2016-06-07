<?php
namespace dix\base\module\generator\controller;

use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;

/**
 * Command which help you generate code
 * @package dix\base\module\generator\commands
 */
class GenerateController extends Controller
{
    public $config;

    public $generators = [];

    public function init()
    {
        parent::init();
    }

    public function actions()
    {
        $actions = [];
        foreach ($this->config as $name => $config)
        {
            $generator = self::createGenerator($config['template']);
            if (!$generator)
            {
                throw new InvalidConfigException('invalid template: ' . $config['template']);
            }
            $generator['config'] = $config;
            $actions[$name] = [
                'class' => 'dix\base\module\generator\command\GenerateAction',
                'generator' => $generator,
            ];
        }

        return $actions;
    }

    /**
     * use ./yii generator/client
     * @param string $message
     */
    public function actionIndex($message = 'hello world from generator module')
    {
        echo $message . "\n";
    }

    protected static function createGenerator($template)
    {
        $mappings = self::defaultGenerators();
        if (isset($mappings[$template]))
        {
            $class_name = $mappings[$template]['class'];
            return Yii::createObject($class_name);
        }

        return null;
    }

    protected static function defaultGenerators()
    {
        return [
            'api' => [
                'class' => 'dix\base\module\generator\api\ApiGenerator',
            ]
        ];
    }

}
