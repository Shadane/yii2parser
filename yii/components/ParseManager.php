<?php

namespace app\components;

use Yii;
use app\models\Account;
use app\models\Market;
use app\models\App;

use app\components\parser\AmazonParser;
use app\components\parser\WindowsphoneParser;
use app\components\parser\AppleParser;

/**
 * Class ParseManager
 * @package app\components
 */
class ParseManager
{
    private $error;
    private $parser;
    private $force = false;
    private $totalCount = 0;

    static function className(){
        return get_called_class();
    }

    public function getError()
    {
        return $this->error;
    }

    public function timer(){
        $time = explode(' ', microtime());
        return $time[0]+$time[1];
    }

    /**
     * На входе - строка с названием маркета
     * На выходе - объект парсера
     * @param $marketName
     * @return AmazonParser|AppleParser|WindowsphoneParser|False
     */
    private static function createParserByName($marketName)
    {
        switch($marketName){
            case 'amazon':
                return new AmazonParser();
                break;
            case 'windowsphone':
                return new WindowsphoneParser();
                break;
            case 'apple':
                return new AppleParser();
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * В этом методе непосредственно происходит сохранение приложения в базу данных.
     * @param $app = массив с аттрибутами приложения
     * @param $model = обьект приложения для обновления(либо null, в этом случае создастся новое)
     * @return bool
     */
    private function internalSave($app, $model)
    {
        $model = ($model)? $model:new App();
        $model->attributes = $app;
        if (!$model->save()){
            $this->error[$model->title] = $model->getErrors();
            return false;
        }
            return true;
    }

    /**
     * Для каждого элемента в массиве Приложений в зависимости от флага updateEveryApp
     * мы либо загружаем из базы данных приложение, либо передаем в эту переменную
     * новый инстанс App. Затем модель и поля на сохранение отправляются в метод
     * internalSave, где и происходит сохранение.
     * @param $apps - массив, состоящий из массивов с полями приложений
     * @param $updateEveryApp - boolean, если true, то из базы загружаются существующие приложения и обновляются.
     */
    protected function save($apps, $updateEveryApp)
    {
        $savedCount = 0;
        foreach ($apps as $app)
        {
//            echo "\n\r".'Time Elapsed:'.$app['timeBeforeRequest'];
            $model = ($updateEveryApp)? App::find()
                                ->where(['title'=>$app['title'], 'account_id'=>$app['account_id'], 'market_id'=>$app['market_id']])
                                ->one()
                                : NULL;
           if ($savedOrNot =  $this->internalSave($app, $model)){
               $savedCount += 1;
               $this->totalCount += 1;
           }
        }
        Yii::info('[Saved : '.$savedCount.'][Total '.$this->totalCount.' apps saved]','parseInfo');

    }

    /**
     * Если хоть одно приложение с таким аккаунтом уже существует,
     * то проверяем опцию force, если оно =false, то пропускаем
     * текущий аккаунт, но если force = true, то запускаем
     * парс и обновление всех записей в каждом аккаунте.
     *
     * Это нужно, к примеру, если мы хотим, чтобы только свежедобавленный маркет обрабатывался.
     * @param $acc
     */
    protected function parseOrSkip($acc)
    {
        $updateEveryAppFlag = false;

        if (App::findOne(['account_id' => $acc->id])){
            if(!$this->force) {
                return;
            }
            $updateEveryAppFlag = true;
        }

        $this->parseByAccPage($acc, $updateEveryAppFlag);


    }

    private function parseByAccPage($acc, $updateEveryAppFlag){
        $this->parser->setAccount($acc);
        $link = $this->parser->getLink();
        do{
            $appList = $this->parser->processAccPage($link);
            $link = $this->parser->getNextPageLink();
            if($apps = $this->parser->processAppList($appList)){

                $this->save($apps, $updateEveryAppFlag);
            }
        }while($link);
            Yii::info('[Account: html had '.$this->parser->getListCount().' apps to parse ]','parseInfo');
            if($apps = $this->parser->getResult()){
                $this->save($apps, $updateEveryAppFlag);
            }

    }

    /**
     * Создается парсер по параметру $marketName,
     * Force(принудительная перезапись, boolean) - кладется в свойство объекта ParseManager
     * По имени маркета находится его ID в базе данных
     * Находим все аккаунты по этому ID маркета
     * Для каждого аккаунта запускаем метод parseOrSkip
     * @param $marketName
     * @param $force
     */
    public function manageParsingByMarket($marketName, $force)
    {
        $this->parser = static::createParserByName($marketName);
        $this->force = $force;
        $marketId = Market::findIdByName($marketName);
        $accounts = Account::findAll(['market_id'=>$marketId]);

        $beginTime = $this->timer();
        foreach ($accounts as $acc)
        {
            Yii::info('[Account start: '.$acc->name.']','parseInfo');
            $this->parseOrSkip($acc);
            Yii::info('[Account done: '.$acc->name.']','parseInfo');
        }
        Yii::info('[FINISH] [Took : '.round($this->timer() - $beginTime,6).' seconds]','parseInfo');


    }
    public function manageParsingPage($url, $accID, $time){

        $acc = Account::findOne($accID);
        $this->parser = static::createParserByName($acc->market->name);
        $apps = $this->parser->processPage($url, $acc, $time);
        echo serialize($apps);
    }
}