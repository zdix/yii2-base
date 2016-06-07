<?php

/* @var $this yii\web\View */
/* @var $generator dix\base\module\generator\api\ApiGenerator */

?>
<?php foreach ($generator->getServiceMethodIDs() as $method): ?>
    public static function <?= $method ?>

    {
        //TODO
    }

<?php endforeach; ?>
