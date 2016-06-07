<?php
namespace dix\base\component;

use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\UrlManager;
use yii\web\UrlRuleInterface;
use yii\base\Object;

class ModuleApiUrlRule extends Object implements UrlRuleInterface
{

    /**
     * Parses the given request and returns the corresponding route and parameters.
     * @param UrlManager $manager the URL manager
     * @param Request $request the request component
     * @return array|boolean the parsing result. The route and the parameters are returned as an array.
     * If false, it means this rule cannot be used to parse this path info.
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();

        if (preg_match('/^(\w+)\/(\d+)\/([\w-]+)\/([a-z0-9-_]+)/', $pathInfo, $matches))
        {
            $module_name = $matches[1] . '-' . $matches[2];
            $controller = $matches[3];
            $action = $matches[4];

            return ["${module_name}/${controller}/${action}", []];
        }

        return false;
    }

    /**
     * Creates a URL according to the given route and parameters.
     * @param UrlManager $manager the URL manager
     * @param string $route the route. It should not have slashes at the beginning or the end.
     * @param array $params the parameters
     * @return string|boolean the created URL, or false if this rule cannot be used for creating this URL.
     */
    public function createUrl($manager, $route, $params)
    {
        return false;
    }
}