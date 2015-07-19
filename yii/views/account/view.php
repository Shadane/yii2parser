<?php

/* @var $this yii\web\View */
/* @var $model app\models\Account */

$this->title = 'Просмотр аккаунта';
$this->params['breadcrumbs'][] = ['label' => 'Аккаунты', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-view">

    <?= $this->render('_detail-view', [
        'model' => $model,
        'title' => $this->title
    ]) ?>

</div>
