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

    private function internalSave($app, $model)
    {
        $model = ($model)? $model:new App();
        $model->attributes = $app;
        if (!$model->save()){
            $this->error[] = $model->getErrors();
        }
    }

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
        foreach ($accounts as $acc)
        {
            $this->parseOrSkip($acc);
        }


    }
}