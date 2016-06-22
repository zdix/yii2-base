<?php
/**
 * This is the default for generating a controller class file.
 */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use dix\base\component\DXUtil;

/* @var $this yii\web\View */
/* @var $generator dix\base\module\generator\api\ApiGenerator */

$space = '        ';
$break_line = "\n";

echo "<?php\n";
?>

namespace <?= $generator->getControllerNamespace() ?>;

<?
foreach ($generator->services as $namespace)
{
    echo 'use ' . substr($namespace, 1) . ';' . $break_line;
}
?>

class <?= StringHelper::basename($generator->controller) ?> extends <?= strpos($generator->base_controller, $generator->getControllerNamespace()) === false ? ('\\' . trim($generator->base_controller, '\\') . "\n") : str_replace($generator->getControllerNamespace(). '\\', '', $generator->base_controller)?>

{

<?php
$params_optional = [];
$params_required = [];
foreach ($generator->getActionIDs() as $action) { ?>
    public function action<?= Inflector::id2camel($action) ?>()
    {
<?php
    $params_optional = $generator->params_optional[$action];
    $params_required = $generator->params_required[$action];
    $params_required_text = '';
    foreach ($params_required  as $param_required)
    {
        $params_required_text .= '\''. $param_required[0] .'\', ';
    }
    $params_required_text = trim(trim($params_required_text), ',');

    if (!empty($params_required))
    {
        echo $space . '$this->checkParams(['. $params_required_text .']);';
        echo $break_line;
        echo $break_line;
    }

//    if ($generator->getToken($action))
//    {
//        echo $space . '$_user_id = $this->user_id;' . $break_line;
//    }

    foreach ($params_required as $param_required)
    {
        $name = $param_required[0];
        $type = $param_required[1];
        $transform_func = DXUtil::getTransformFunc($type);
        echo $space . '$' . $name . ' = ' . $transform_func . '($this->params[\'' . $name . '\']);' . $break_line;
    }
    if (!empty($params_required))
    {
        echo $break_line;
    }

    foreach ($params_optional as $param_optional)
    {
        $name = $param_optional[0];
        $type = $param_optional[1];
        $default = $param_optional[2];
        $transform_func = DXUtil::getTransformFunc($type);

        $false_part = $default;
        if ($type == 's' && $default !== null && $default !== 'null')
        {
            $false_part = '\'' . $default . '\'';
        }

        echo $space . '$' . $name . ' = isset($this->params[\''. $name .'\']) ? ' . $transform_func . '($this->params[\'' . $name . '\']) : '. $false_part .' ;' . $break_line;
    }
    if (!empty($params_optional))
    {
        echo $break_line;
    }

    $responses = $generator->getResponse($action);
    echo $space . '$_data = null;' . $break_line;
    if (is_array($responses))
    {
        foreach ($responses as $key => $method)
        {
            if ($key != 'null')
            {
                echo $space . '$_data[\'' . $key . '\'] = ' . DXUtil::getServiceMethodCall($method) . ";" . $break_line;
            }
            else
            {
                echo $space . DXUtil::getServiceMethodCall($method) . ";" . $break_line;
            }

        }
    }
    else
    {
        $method = $responses;
        echo $space . '$_data = ' . DXUtil::getServiceMethodCall($method) . ";" . $break_line;
    }
    echo $break_line;

    echo $space . '$this->finishSuccess($_data);';
    echo $break_line;
    ?>
    }

<?php } ?>
}