<?php
/* @var $this yii\web\View */
$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Добро Пожаловать!</h1>
        <h2>Как спарсить аккаунт?</h2>

        <p class="lead">Добавьте аккаунт в административной панели</p>
        <p class="lead">Запустите парс из консоли: <code>php yii parse 'marketname'</code></p>
        <p class="lead">Если вы хотите обновить все приложения во всех аккаунтах маркета, то запустите <code>php yii parse 'marketname' --force</code> </p>
        <p class="lead">Если опция <code>--force</code> отключена, то проверяется наличие хотябы одного приложения у аккаунта,
            и если оно есть, то этот аккаунт пропускается, а если его нет - значит это новый аккаунт и в него парсятся
            приложения. Со включенной опцией каждое приложение в каждом аккаунте насильно обновляется. </p>
        <p class="lead">Возможные 'marketname' по дефолту: 'apple', 'windowsphone', 'amazon'</p>


        <p class="lead">Просмотреть логи парсинга можно по адресу yii/runtime/logs/parse.log, а также <?=\kartik\helpers\Html::a('Тут', ['logs'])?></p>


    </div>
    </div>


