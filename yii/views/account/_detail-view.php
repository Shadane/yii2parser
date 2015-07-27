<?php
use kartik\detail\DetailView;
use app\models\OutputHelper;

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
        'items' => OutputHelper::mapModelList('market'),

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