<?php
namespace dix\base\module\generator;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;

class Module extends BaseModule implements BootstrapInterface
{

    public $config;

    public function init()
    {
        parent::init();
    }

    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $app->controllerMap[$this->id] = [
                'class' => 'dix\base\module\generator\controller\GenerateController',
                'module' => $this,
                'config' => $this->config,
            ];
        }
    }

    public function getControllerPath()
    {
        return 'dix\base\module\generator\controller';
    }
}

