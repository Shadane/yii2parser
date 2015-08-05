<?php

namespace app\commands;

use app\components\ParseManager;
use app\components\streamhandle\StreamHandler;
use yii\console\Controller;
use phpQuery;
use Yii;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ParseController extends Controller
{
    /* --------------------------------------------
     * Переменная force служит как переключатель -
     * обновлять ли приложения или пропускать
     * аккаунты у которых уже есть записи.
     * -------------------------------------------- */
    public $force=false;

    /**
     * @param $marketName
     * В этом экшне создается и запускается парсменеджер.
     * Также логируются ошибки валидации если они есть.
     */
    public function actionIndex($marketName)
    {
        Yii::info('[START]','parseInfo');

        $parseManager = new ParseManager();

        Yii::info('['.$this->className().'] : '.$parseManager::className().' created, starting [Market : '.$marketName.'] parse','parseInfo');

        $parseManager->manageParsingByMarket($marketName, $this->force);
        if ($errs = $parseManager->getError()){
            Yii::info($errs, 'parseInfo');
        }
        Yii::info('[FINISH] [Took : '.Yii::getLogger()->elapsedTime.' seconds]','parseInfo');

    }

    public function options($actionID)
    {
        return ['force'];
    }

    /**
     * Этот процесс запускается в качестве потока, на входе получает Массив ссылок и идентификатор аккаунта
     * На выходе отдает сериализованный массив ответов(полей приложений)
     * @param $url
     * @param $accID
     * @throws ErrorException
     */
    public function actionPage($url, $accID){
        $parseManager = new ParseManager();
        echo $parseManager->manageParsingUrls($url, $accID);

        throw new ErrorException('[FINISH] [Took : '.Yii::getLogger()->elapsedTime.' seconds]');

    }
}
