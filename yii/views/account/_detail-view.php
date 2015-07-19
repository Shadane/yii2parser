<?php
use kartik\detail\DetailView;
use yii\helpers\ArrayHelper;
use app\models\Market;

/* -----------------------------
 *   Атрибуты для DetailView
 * -----------------------------
 */
$attributes = [
    [
        'attribute' => 'id',
        'format' => 'raw',
        'value' => '<kbd>' . $model->id . '</kbd>',
        'displayOnly' => true
    ],
    [
        'attribute' => 'name'
    ],
    [
        'attribute' => 'market_id',
        'label' => 'Маркет',
        'format' => 'raw',
        'value' => $model->market->name,
        'type' => DetailView::INPUT_DROPDOWN_LIST,
        'items' => ArrayHelper::map(Market::find()->orderBy('id')->asArray()->all(), 'id', 'name'),

    ],
];
/* -----------------------------
 *   Настройки для DetailView
 * -----------------------------
 */
$settings = [
    'model' => $model,
    'attributes' => $attributes,
    'mode' => 'edit',
    'panel'=>[
        'heading'=>false,
        'type'=>DetailView::TYPE_PRIMARY,
        'footer' =>'',
        'footerOptions'=>[
            'template' => '{buttons}'
        ]
    ],
    'buttonContainer' => [
        'class' => 'text-center'
    ],
    'condensed' => false,
    'responsive' => false,
    'hover' => true,
    'hAlign'=>'center',
    'vAlign'=>'middle',
    'fadeDelay'=>500,
    'deleteOptions' => [ // your ajax delete parameters
        'params' => ['id' => $model->id, 'custom_param' => true],
        'url' => ['delete']
    ],
    'container' => ['id'=>'detail-view']
];

/* -----------------------------
 *      Вывод DetailView
 * -----------------------------
 */
echo DetailView::widget($settings);