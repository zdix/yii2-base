<?php
/**
 * This is the default for generating a service class file.
 */

use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator dix\base\module\generator\api\ApiGenerator */

echo "<?php\n";
?>

namespace <?= $generator->getServiceNamespace() ?>;

class <?= StringHelper::basename($generator->service) ?>

{
