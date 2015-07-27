<?php

use yii\bootstrap\Carousel;
use app\models\OutputHelper;
?>
<div class="app-view">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-sm-12 col-xs-12">
                <h3>
                    <!-- PRICE -->
                    <?= OutputHelper::formatPrice($model->price) ?>
                </h3>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12 description-container">
                <!-- DESCRIPTION -->
                <?= OutputHelper::formatDescription($model->description, '<div class="readmore">', '</div>') ?>
            </div>


            <div class="carousel-container col-md-12 col-sm-12 col-xs-12">
                <!-- IMG CAROUSEL -->
                <?= Carousel::widget([
                    'items' => OutputHelper::urlsToCarousel($model->url_img),
                ]);
                ?>

            </div>
        </div>
    </div>
</div>





