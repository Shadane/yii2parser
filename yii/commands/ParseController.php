<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\ParseManager;
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
        $parseManager = new ParseManager();
        $parseManager->manageParsingByMarket($marketName, $this->force);
        if ($errs = $parseManager->getError()){
            Yii::info($errs, 'parseInfo');
        }
    }

    public function options($actionID)
    {
        return ['force'];
    }
}
