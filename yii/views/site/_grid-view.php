<?php
use kartik\grid\GridView;


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $gridColumns,
    'id' => 'myGrid',
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    // set your toolbar
    'toolbar' => $gridToolbar,
    'pjax' => false,
    // parameters from the demo form
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'responsive' => true,
    'hover' => true,
    'panel' => [
        'type' => GridView::TYPE_PRIMARY,
        'heading' => $gridTitle,
    ]
]);
