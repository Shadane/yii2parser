<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\Market;
use yii\helpers\Url;
use yii\bootstrap\Modal;


/* @var $this yii\web\View */
/* @var $searchModel app\models\AccountSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->title = 'Аккаунты';
$this->params['breadcrumbs'][] = $this->title;

/* -----------------------------------------------------------
 *   Контейнер с всплывающим сообщением об удалении аккаунта
 * ----------------------------------------------------------- */
?>
    <div class="<?= $showAlert ? 'show' : 'hide' ?>">

        <?= $this->render('/site/_alert', [
            'message' => Yii::$app->session->getFlash('info')
        ]); ?>

    </div>

<?php
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
        /* редактируемое название аккаунта в таблице */
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'name',
        'label' => 'Имя аккаунта',
        'vAlign' => 'middle',
        'width' => '45%',
        'readonly' => false,
        'refreshGrid' => false,
        'editableOptions' =>
            [
                'header' => 'аккаунт',
                'size' => 'md',
            ],

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
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item? Parsed Applications for this account will be deleted as well'),
                        'method' => 'post',
                        'params' => ['custom_param' => true, 'id' => $key],

                    ]

                ]);
            },

        ],
    ],
];

?>

<?php
/* ------------------------------------------------------
 *  Переменная gridToolbar - тулбар в наш виджет GridView
 * ------------------------------------------------------ */
$gridToolbar = [
    ['content' =>
        Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'], ['type' => 'button', 'title' => 'Добавить аккаунт', 'class' => 'btn btn-success']) . ' ' .
        Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['index'], ['data-pjax' => 0, 'class' => 'btn btn-default', 'title' => 'reset'])
    ],
    '{toggleData}',
];
/* ------------------------------------------------------
 *   Далее - контейнер, в который рендерится partial
 * ------------------------------------------------------ */
?>
    <div class="account-index">
        <?= $this->render('/site/_grid-view', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'gridColumns' => $gridColumns,
            'gridToolbar' => $gridToolbar,
            'gridTitle' => 'Список Аккаунтов'
        ]); ?>
    </div>
<?php
/* -------------------------------------------------------
 *  Модальное окно со скриптом, загружающим в него данные.
 *  Скрипт срабатывает при нажатии на кнопку редактиро-
 *  вания в колонке actionColumn нашего gridView.
 * ------------------------------------------------------- */
Modal::begin([
    'id' => 'detailModal',
    'header' => '<h4 class="modal-title text-center">Редактирование аккаунта</h4>',
]);
Modal::end();
?>

<?php $this->registerJs("
            $('.activity-edit-link').click(function() {
                $.get(
                    '" . Url::toRoute(['update']) . "',
                    {
                           id: $(this).closest('tr').data('key')
                    },
                     function (data) {
                           $('.modal-body').html(data);
                     }
                );
            });
");

