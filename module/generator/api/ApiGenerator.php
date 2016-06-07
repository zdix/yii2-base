<?php
namespace dix\base\module\generator\api;


use dix\base\component\DXUtil;
use dix\base\module\generator\CodeFile;
use dix\base\module\generator\command\Generator;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

class ApiGenerator extends Generator
{
    //config
    public $config;
    public $api;
    public $base_controller;
    public $controller_path;
    public $action_list;

    public $controller_map;
    public $action_map;
    public $params_optional_map;
    public $params_required_map;
    public $response_map;
    public $service_map;
    public $method_map;
    public $token_map;

    //filtered by controller
    public $controller;
    public $actions;
    public $params_optional;
    public $params_required;
    public $responses;
    public $services;
    public $methods;
    public $tokens;

    public $service;
    public $methodIDs;

    /**
     * the '}' in class content
     */
    const CLASS_CONTENT_SUF = '}';

    /**
     * @return string name of the code generator
     */
    public function getName()
    {
        return "Api Generator";
    }

    /**
     * Generates the code based on the current user input and the specified code template files.
     * This is the main method that child classes should implement.
     * Please refer to [[\yii\gii\generators\controller\Generator::generate()]] as an example
     * on how to implement this method.
     * @return CodeFile[] a list of code files to be created.
     */
    public function generate()
    {
        $this->initDefaultConfig();
        $this->parseConfig();
        $this->convertActionListToStruct($this->action_list);

        $files = [];
        foreach ($this->controller_map as $type => $controller)
        {
            $this->controller = $controller;
            $this->actions = $this->action_map[$type];
            $this->responses = $this->response_map[$type];
            $this->tokens = $this->token_map[$type];
            $this->services = $this->service_map[$type];
            $this->methods = $this->method_map[$type];
            $this->params_optional = $this->params_optional_map[$type];
            $this->params_required = $this->params_required_map[$type];

            //generate controller files
            $files[] = new CodeFile(
                $this->getControllerFile(),
                $this->render('controller.php')
            );
        }

        return $files;
    }

    private function parseConfig()
    {
        if (isset($this->config['baseController']))
        {
            $this->base_controller = $this->config['baseController'];
        }
        $this->controller_path = $this->config['controllerPath'] . "\\";
        $this->action_list = $this->config['actions'];
        echo "Configs:" . "\n";
        echo "  [Config] Controller Path       : " . $this->controller_path . "\n";
        echo "  [Config] Base Controller Class : " . $this->base_controller . "\n";
        echo "\n";
    }

    private function convertActionListToStruct($action_list)
    {
        $controllers = [];
        $actions = [];
        $params_optional = [];
        $params_required = [];
        $responses = [];
        $services = [];
        $methods = [];
        $tokens = [];

        $length = count($action_list);
        $index = 0;
        for (; $index < $length; $index++)
        {
            $api = $action_list[$index];
            $action = $api['action'];
            $params = $api['params'];
            $response = $api['response'];
            $token = $api['token'];

            $type = Inflector::id2camel(substr($action, 0, strpos($action, '/'))); // common/check-update -> common
            $action = substr($action, strpos($action, '/') + 1, strlen($action));// common/check-update -> check-update
            $controller = $this->controller_path . ucwords($type) . 'Controller';

            $params_required[$type][$action] = [];
            $params_optional[$type][$action] = [];
            $params_length = count($params);
            $i = 0;
            for (; $i < $params_length; $i++)
            {
                $param = $params[$i];
                $param = str_replace(' ', '', $param);
                if (!self::validateParam($param))
                {
                    throw new InvalidParamException($param);
                }

                $param_data = [];

                $param_exploded = explode(',', str_replace('|', ',', trim($param)));
                $param_data[] = trim($param_exploded[0]); // name
                $param_data[] = trim($param_exploded[1]); // type
                $key_default_value = isset($param_exploded[2]) ? trim($param_exploded[2]) : null;
                $param_data[] = $key_default_value;


                if ($key_default_value === null) // if no option value, then it's required
                {
                    $params_required[$type][$action][] = $param_data;
                }
                else
                {
                    $params_optional[$type][$action][] = $param_data;
                }
            }

            if (!isset($methods[$type]))
            {
                $methods[$type] = [];
            }

            if (!isset($services[$type]))
            {
                $services[$type] = [];
            }
            
            $service_function_list = is_array($response) ? $response : [$response];
            foreach ($service_function_list as $service_function)
            {
                $service_exploded = explode('::', $service_function);
                $service = $service_exploded[0];
                if (strpos($service, '!') === 0)
                {
                    continue;
                }

                $method = $service_exploded[1];
                if (!array_key_exists($type, $services) || !in_array($service, $services[$type]))
                {
                    $services[$type][] = $service;
                }
                $methods[$type][$service][] = $method;
            }

            $actions[$type][] = $action;
            $controllers[$type] = $controller;
            $responses[$type][$action] = $response;
            $tokens[$type][$action] = $token;

            $this->action_map = $actions;
            $this->controller_map = $controllers;
            $this->service_map = $services;
            $this->method_map = $methods;
            $this->params_optional_map = $params_optional;
            $this->params_required_map = $params_required;
            $this->response_map = $responses;
            $this->token_map = $tokens;

        }

    }

    private function getMethodsWillGeneratedInService($methods)
    {
        $methods_name_local = [];
        if (is_file($this->getServiceFile()))
        {
            $class = new \ReflectionClass($this->service);
            $methods_local = $class->getMethods();
            foreach ($methods_local as $method_local)
            {
                $methods_name_local[] = $method_local->getName();
            }
        }

        foreach ($methods as $key => $method)
        {
            $name = substr($method, 0, strpos($method, '('));
            if (in_array($name, $methods_name_local))
            {
                unset($methods[$key]);
            }
        }

        return $methods;

    }

    public function renderServiceContent()
    {
        $path = $this->getServiceFile();
        $content = '';
        if (is_file($path))
        {//if this service already exists, contentPre = file_get_contents . '}';
            $content .= $this->getServiceContentPre($path);
        }
        else
        {
            $content .= $this->render('service.php');
        }

        if (count($this->methods) > 0)
        {
            $content .= $this->render('serviceMethod.php');
        }
        $content .= self::CLASS_CONTENT_SUF;
        return $content;
    }

    public function getServiceContentPre($path)
    {
        $contentPre = '';
        $class = new \ReflectionClass($this->service);
        $lastLine = $class->getEndLine() - 2;

        $fp = fopen($path, 'r');
        $line = 0;

        while (!feof($fp))
        {
            if ($lastLine >= $line)
            {
                $contentPre .= fgets($fp);
            }
            else
            {
                break;
            }
            $line++;
        }

        return $contentPre;
    }

    /**
     * Normalizes [[actions]] into an array of action IDs.
     * @return array an array of action IDs entered by the user
     */
    public function getActionIDs()
    {
        return $this->actions;
    }

    /**
     * @return string the namespace of the controller class
     */
    public function getControllerNamespace()
    {
        $name = StringHelper::basename($this->controller);
        return ltrim(substr($this->controller, 0, -(strlen($name) + 1)), '\\');
    }

    /**
     * @return string the controller class file path
     */
    public function getControllerFile()
    {
        return Yii::getAlias('@' . str_replace('\\', '/', $this->controller)) . '.php';
    }

    /**
     * @return string the controller ID
     */
    public function getControllerID()
    {
        $name = StringHelper::basename($this->controller);
        return Inflector::camel2id(substr($name, 0, strlen($name) - 10));
    }

    public function getServiceNamespace()
    {
        $name = StringHelper::basename($this->service);

        return ltrim(substr($this->service, 0, -(strlen($name) + 1)), '\\');
    }

    public function getServiceFile()
    {
        return Yii::getAlias('@' . substr(str_replace('\\', '/', $this->service), 1)) . '.php';
    }

    public function getServiceMethodIDs()
    {
        return $this->methodIDs;
    }

    public function getToken($action)
    {
        return $this->tokens[$action];
    }

    public function getResponse($action)
    {
        return $this->responses[$action];
    }

    protected static function validateParam($p)
    {
        $line_pos = strpos($p, '|');
        if (!empty($p) && $line_pos !== false)
        {
            return true;
        }
        return false;
    }

    private function initDefaultConfig()
    {
        $this->base_controller = 'app\modules\client\v100\controllers';
    }

}
