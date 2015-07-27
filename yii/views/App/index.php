<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\OutputHelper;
use yii\helpers\Url;
use yii\bootstrap\Modal;


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
        'width' => '15px',
        'header' => '#',
        'headerOptions' => ['class' => 'kartik-sheet-style']
    ],
    [
        /* иконка приложения */
        'attribute' => 'url_icon',
        'label' => 'Иконка',
        'format' => 'image',
        'width' => '60px',
        'filter' => false,
        'contentOptions' => ['class' => 'url-icon']
    ],
    [
        /* раскрываемое окно с детальным описанием приложения и скриншотами */
        'class' => 'kartik\grid\ExpandRowColumn',
        'width' => '50px',
        'detail'=>function ($model, $key, $index, $column) {
            return Yii::$app->controller->renderPartial('_expand-row-details', ['model'=>$model]);
        },
        'value' => function ($model, $key, $index, $column) {
            return GridView::ROW_COLLAPSED;
        },
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'expandOneOnly' => true,
    ],
    [
        /* редактируемое название приложения в таблице */
        'attribute' => 'title',
        'label' => 'Название',
        'format' => 'html',
        'vAlign' => 'middle',
        'value' => function ($model, $key, $index) {
            return Html::a($model->title, $model->url, [
                'class' => 'view-source-link',
                'title' => Yii::t('app', 'View Source (link to external website)')
            ]);
        },
    ],
    [
        /* идентификатор маркета в таблице, рендерится как название маркета */
        'attribute' => 'market_id',
        'label' => 'Маркет',
        'value' => function ($model, $key, $index, $widget) {
            return "<span class='badge'> </span>  <code>" . $model->market->name . '</code>';
        },
        'width' => '20%',
        'filterType' => GridView::FILTER_SELECT2,
        /* данные на фильтр берутся из query */
        'filter' => OutputHelper::mapModelList('market'),
        'filterInputOptions' => ['placeholder' => Yii::t('app', 'Any')],
        'filterWidgetOptions' => ['pluginOptions' => ['allowClear' => true],],
        'vAlign' => 'middle',
        'format' => 'raw',
    ],
    [
        /* идентификатор аккауна в таблице, рендерится как название аккаунта */
        'attribute' => 'account_id',
        'label' => 'Аккаунт',
        'value' => function ($model, $key, $index, $widget) {
            return "<span class='badge'> </span>  <code>" . $model->account->name . '</code>';
        },
        'width' => '20%',
        'filterType' => GridView::FILTER_SELECT2,
        /* данные на фильтр берутся из query */
        'filter' => OutputHelper::mapModelList('account'),
        'filterInputOptions' => ['placeholder' => Yii::t('app', 'Any')],
        'filterWidgetOptions' => ['pluginOptions' => ['allowClear' => true],],
        'vAlign' => 'middle',
        'format' => 'raw',
    ]
];

/* ------------------------------------------------------
 *  Переменная gridToolbar - тулбар в наш виджет GridView
 * ------------------------------------------------------ */
$gridToolbar = [
    ['content' =>
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

<?php
/* -------------------------------------------------------
 *  Модальное окно со скриптом, загружающим в него данные.
 *  Скрипт срабатывает при нажатии на кнопку редактиро-
 *  вания в колонке actionColumn нашего gridView.
 * ------------------------------------------------------- */

$this->registerJs("
    $('#myGrid').on('kvexprow.toggle',function(){
        jQuery('.kv-expanded-row .readmore').readmore({
        collapsedHeight: 90,
        heightMargin: 16,
        moreLink: '<a href=#>Читать полностью</a>',
        lessLink: '<a href=#>Скрыть</a>',
    });
});
");

