<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\Market;
use app\models\Account;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AppSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Приложения';
$this->params['breadcrumbs'][] = $this->title;


/* --------------------------------------------------------
 *  Переменная gridColumns - колонки в наш виджет GridView
 * -------------------------------------------------------- */
$gridColumns = [
    [
        /* порядковый номер в таблице */
        'class' => 'kartik\grid\SerialColumn',
        'contentOptions' => ['class' => 'kartik-sheet-style'],
        'width' => '36px',
        'header' => '',
        'headerOptions' => ['class' => 'kartik-sheet-style']
    ],
    [
        'class'=>'kartik\grid\ExpandRowColumn',
        'width'=>'50px',
        'value'=>function ($model, $key, $index, $column) {
            return GridView::ROW_COLLAPSED;
        },
        'detail'=>function ($model, $key, $index, $column) {
            return Yii::$app->controller->renderPartial('_expand-row-details', ['model'=>$model]);
        },
        'headerOptions'=>['class'=>'kartik-sheet-style'],
    'expandOneOnly'=>true
],
    [
        /* редактируемое название приложения в таблице */
        'attribute' => 'title',
        'label' => 'Название',
        'width' =>'30%'
    ],
    [
        /* идентификатор маркета в таблице, рендерится как название маркета */
        'attribute' => 'market_id',
        'label' => 'Маркет',
        'value' => function ($model, $key, $index, $widget) {
            return "<span class='badge'> </span>  <code>" . $model->market->name . '</code>';
        },
        'width' => '45%',
        'filterType' => GridView::FILTER_SELECT2,
        /* данные на фильтр берутся из маркета */
        'filter' => ArrayHelper::map(Market::find()->orderBy('id')->asArray()->all(), 'id', 'name'),
        'filterInputOptions' => ['placeholder' => 'Любой'],
        'filterWidgetOptions' => ['pluginOptions' => ['allowClear' => true],],
        'vAlign' => 'middle',
        'format' => 'raw',
    ],
    [
        /* идентификатор маркета в таблице, рендерится как название маркета */
        'attribute' => 'account_id',
        'label' => 'Аккаунт',
        'value' => function ($model, $key, $index, $widget) {
            return "<span class='badge'> </span>  <code>" . $model->account->name . '</code>';
        },
        'width' => '45%',
        'filterType' => GridView::FILTER_SELECT2,
        /* данные на фильтр берутся из маркета */
        'filter' => ArrayHelper::map(Account::find()->orderBy('id')->asArray()->all(), 'id', 'name'),
        'filterInputOptions' => ['placeholder' => 'Любой'],
        'filterWidgetOptions' => ['pluginOptions' => ['allowClear' => true],],
        'vAlign' => 'middle',
        'format' => 'raw',
    ],

    [
        /* действия в таблице - редактировать и удалить */
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{update} {delete}',
        'urlCreator' => function ($action, $model, $key, $index) {
            if ($action == 'update') {
                return Url::toRoute(['account/update', 'id' => $key]);
            }
            if ($action == 'delete') {
                return Url::toRoute(['account/delete']);
            }
        },
        'buttons' => [
            /* update запускает модальное окно */
            'update' => function ($url, $model, $key) {
                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                    'class' => 'activity-edit-link',
                    'title' => Yii::t('yii', 'Update'),
                    'data-toggle' => 'modal',
                    'data-target' => '#detailModal',
                    'data-id' => $key,

                ]);
            },
            /* delete переделан для передачи через $_POST */
            'delete' => function ($url, $model, $key) {
                return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                    'class' => 'activity-delete-link',
                    'title' => Yii::t('yii', 'Delete'),
                    'data' => [
                        'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                        'params' => ['custom_param' => true, 'id' => $key],

                    ]

                ]);
            },

        ],
    ],
];

/* ------------------------------------------------------
 *  Переменная gridToolbar - тулбар в наш виджет GridView
 * ------------------------------------------------------ */
$gridToolbar = [
    ['content' =>
//        Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'], ['type' => 'button', 'title' => 'Добавить аккаунт', 'class' => 'btn btn-success']) . ' ' .
        Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['index'], ['data-pjax' => 0, 'class' => 'btn btn-default', 'title' => 'reset'])
    ],
    '{toggleData}',
];
/* ------------------------------------------------------
 *   Далее - контейнер, в который рендерится partial
 * ------------------------------------------------------ */
?>
<div class="app-index">
    <?= $this->render('/site/_grid-view', [
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
        'gridColumns' => $gridColumns,
        'gridToolbar' => $gridToolbar,
        'gridTitle' => 'Список Приложений'
    ]); ?>
</div>

?>
<div class="app-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create App', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'market_id',
            'account_id',
            'title',
            'price',
            // 'url:url',
            // 'url_icon:url',
            // 'url_img:url',
            // 'description:ntext',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
