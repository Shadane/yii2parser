<?php
use kartik\widgets\Alert;

echo Alert::widget([
    'type' => Alert::TYPE_INFO,
    'icon' => 'glyphicon glyphicon-info-sign',
    'body' => $message,
    'showSeparator' => true,
    'delay' => 4000
]);