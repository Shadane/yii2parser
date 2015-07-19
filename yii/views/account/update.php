<?php

use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $model app\models\Account */

$this->title = 'Редактирование аккаунта';
$this->params['breadcrumbs'][] = ['label' => 'Аккаунт', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="account-update">


    <?= $this->render('_detail-view', [
        'model' => $model,
        'title' => $this->title
    ]) ?>

</div>