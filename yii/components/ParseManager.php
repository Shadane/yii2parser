<?php

namespace app\components;

use Yii;
use app\models\Account;
use app\models\Market;
use app\models\App;

/**
 * Class ParseManager
 * @package app\components
 */
class ParseManager
{
    private $error;
    private $parser;
    private $force = false;

    public function getError()
    {
        return $this->error;
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
     */
    private function internalSave($app, $model)
    {
        $model = ($model)? $model : new App();
        $model->attributes = $app;
        if (!$model->save()){
            $this->error[$model->title] = $model->getErrors();
        }
    }

    /**
     *
     * @param $apps - массив, состоящий из массивов с полями приложений
     * @param $updateEveryApp - boolean, если true, то из базы загружаются существующие приложения и обновляются.
     */
    private function save($apps, $updateEveryApp)
    {
        foreach ($apps as $app)
        {
            $model = ($updateEveryApp)? App::find()
                                ->where(['title'=>$app['title'], 'account_id'=>$app['account_id'], 'market_id'=>$app['market_id']])
                                ->one()
                                : NULL;
            $this->internalSave($app, $model);
        }
    }

    private function parseOrSkip($acc)
    {
        /*
         * Если хоть одно приложение с таким аккаунтом уже существует,
         * то проверяем опцию force, если оно =false, то пропускаем
         * текущий аккаунт, но если force = true, то запускаем
         * парс и обновление всех записей в каждом аккаунте.
         */
        $updateEveryAppFlag = false;

        if (App::findOne(['account_id' => $acc->id])){
            if(!$this->force) {
                return;
            }
            $updateEveryAppFlag = true;
        }

        $apps = $this->parser->parseByAccount($acc);
        return $this->save($apps, $updateEveryAppFlag);

    }

    public function manageParsingByMarket($marketName, $force)
    {
        $this->parser = static::createParserByName($marketName);
        $this->force = $force;
        $marketId = Market::findIdByName($marketName);
        $accounts = Account::findAll(['market_id'=>$marketId]);
        print_r($accounts);
        foreach ($accounts as $acc)
        {
            $this->parseOrSkip($acc);
        }


    }
}