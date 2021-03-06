<?php

namespace app\components\parser;

use app\models\Account;
use app\models\OutputHelper;
use Yii;
use app\components\MyCurl;
use phpQuery;

abstract class BaseParser
{
    /* Следующие константы реализовывать в парсере определенного сайта */


    /* -------------------------------------------------------
     *         ссылка на хоста (аля www.dfsdfs.com)
     * -------------------------------------------------------
     *              abstract const BASE_URL;
     * ------------------------------------------------------- */


    /* -------------------------------------------------------
     *         path до аккаунта с вставленным в него
     *      PLACEHOLDER на месте идентификатора аккаунта
     * -------------------------------------------------------
     *              abstract const ACC_LINK;
     * ------------------------------------------------------- */

    /* -------------------------------------------------------
     *         селектор для выборки списка приложений
     *           с главной страницы аккаунта
     * -------------------------------------------------------
     *          abstract const SELECTOR_APPLIST;
     * ------------------------------------------------------- */

    /* -------------------------------------------------------
     *         Блок селекторов полей приложения
     * -------------------------------------------------------
     *           abstract const SELECTOR_TITLE;
     *           abstract const SELECTOR_DESC;
     *           abstract const SELECTOR_PRICE;
     *           abstract const SELECTOR_URL_ICON;
     *           abstract const SELECTOR_IMAGES;
     * ------------------------------------------------------- */


    /* --------------------------------------------------
     * Переменные $curl и $account тут для того, чтобы
     * не приходилось их передавать между методами
     * -------------------------------------------------- */
    protected $maxRetry = 20;
    /**
     * @var MyCurl
     */
    protected $curl;
    /**
     * @var Account
     */
    protected $account;
    /* в переменную $app записываются поля на сохранение в БД */
    protected $app;

    /* --------------------------------------------------
     * Переменная $apps содержит в себе готовые массивы
     * приложений, которые в дальнейшем можно достать
     * и сохранить в базу данных.
     * -------------------------------------------------- */
    protected $apps = [];

    /* счетчик приложений в applist */
    protected $listCount = 0;

    /*
     * Блок абстрактных методов, в которых потом будут по селекторам доставаться поля приложений
     */
    abstract protected function parseTitle($data);

    abstract protected function parseDescription($data);

    abstract protected function parsePrice($data);

    abstract protected function parseUrlIcon($data);

    abstract protected function parseAppList($data);

    abstract protected function parseUrl($app);

    abstract protected function parseImages($data);

    /* Абстрактный метод, в котором создается ссылка на следующую страницу приложений на аккаунте */
    abstract public function getNextPageLink();

    /* Абстрактный метод, в котором будет декодироваться ответ, полученный в результате curl запроса */
    abstract protected function responseDecode($response);

    /* В этом методе будет обрабатываться список приложений с аккаунта на сайте */
    abstract public function processAppList($appList);

    /* Метод, в котором будет обрабатываться или каким-то образом доставаться массив приложений */
    abstract public function getResult();

    function className(){
        return get_called_class();
    }

    /* При создании парсеров будет создаваться объект Curl */
    public function __construct()
    {
        $this->curlInit();
    }

    /* Добавляем отпарсенное приложение в массив приложений */
    protected function appPush($app)
    {
        $this->apps[] = $app;
    }

    /* Достаем массив, состоящий из массивов с полями приложений */
    public function getApps()
    {
        return $this->apps;
    }

    /* Достаем количество приложений в изначальном списке в аккаунте */
    public function getListCount()
    {
        return $this->listCount;
    }

    /* Создается объект MyCurl и прописываются настройки */
    protected function curlInit()
    {
        $this->curl = new MyCurl();
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true);
    }


    /** --------------------------------------------------------
     *                     parseSingleApp
     * --------------------------------------------------------
     * Прогоняем нашу полученную от cUrl страницу по селекторам
     * и записываем значения в свойство объекта парсера.
     * -------------------------------------------------------- */
    protected function parseSingleApp($data)
    {
        $this->app['title'] = $this->parseTitle($data);
        $this->app['description'] = $this->parseDescription($data);
        $this->app['price'] = $this->parsePrice($data);
        $this->app['url_icon'] = $this->parseUrlIcon($data);
        $this->app['url_img'] = $this->parseImages($data);
        $this->app['market_id'] = $this->account->market_id;
        $this->app['account_id'] = $this->account->id;

    }

    public function setAccount($acc){
        $this->account = $acc;
    }

    /** --------------------------------------------------------
     *                     getLink
     * --------------------------------------------------------
     * Соединяет константы с сылками в нужном нам порядке и
     * меняет PLACEHOLDER на название аккаунта.
     * -------------------------------------------------------- */
    public function getLink()
    {
        return static::BASE_URL . str_replace('PLACEHOLDER', rawurlencode($this->account->name), static::ACC_LINK);
    }


//    /** -----------------------------------------------------------
//     *                   processAccPage
//     * -----------------------------------------------------------
//     *  Получает список приложений по переданной ссылке, создает
//     * ссылку на следующюю страницу, и запускает обработку
//     * полученного списка приложений. Возвращает ссылку
//     * на следующую страницу.
//     *
//     * Если списка приложений не получает, то возвращает false
//     * в метод parseByAccount, и парсинг текущего аккаунта
//     * завершается.
//     * ----------------------------------------------------------- */
//    public function processAccPage($link)
//    {
//        $retry=0;
//        do{
//            $appList = $this->getApplist($link);
//            $this->listCount += count($appList);
//            $retry++;
//        }while(!count($appList) && $this->maxRetry > $retry);
//        if(!count($appList)) {
//            Yii::error('[GETTING APPLIST FAILED: probably captcha problem]','parseInfo');
//            return false;
//        }
//        return $appList;
//    }

    /**
     * Проверяет поля, полученные при парсинге,
     * если среди них есть пустые, то во-
     * звращает false, иначе true.
     * @return bool
     */
    protected function checkIntegrity($retry)
    {
        foreach ($this->app as $fieldName => $field) {
            if (!$field) {
                Yii::info('[Parsing failure: EMPTY FIELD >>>' . $fieldName . '<<<]. Retrying(' . ($this->maxRetry - $retry) . ') ', 'parseInfo');
//                sleep(1);
                return false;
            }
        }
            Yii::info('grabbed app: ' . $this->app['title'], 'parseInfo');
            return true;
    }

    /**
     * По ссылке с помощью curl получаем нужную нам страницу
     * и декодируем ответ.
     * @param $link
     * @return mixed
     */
    protected function processUrl($link)
    {
//
        /* также при каждом запросе выставляется новый useragent, точно проверить сложно,
        но после этого перестало кидать на страницу captcha */
        $retry = 0;
        do{
            $retry++;
            $this->curl->setOption(   CURLOPT_USERAGENT, 'dfsdf sdfksjdf '.mt_rand(212312,123232312).' weiwier 33 '.mt_rand(12314,212312).' '.mt_rand(0,12312).' dfoef/ '.mt_srand( time() ) .' sdfsdfsd'  );
            if ($html = $this->curl->get($link)) {
                return $this->responseDecode($html);
            }else {
                 Yii::error('[' . $this->account->name .  ',  code: '.$this->curl->responseCode.' . Retrying('.($this->maxRetry - $retry).') in 2 sec.] : ERROR processing link: ' . $link, 'parseInfo');
                 sleep(2);
            }
        }while(!$html && $retry < $this->maxRetry);

        Yii::error('[FAILED: RESPONSE EMPTY][' . $this->account->name .  ',  code: '.$this->curl->responseCode.' ]', 'parseInfo');
        return false;
    }

    /**
     * Получаем по ссылке страницу с сервера
     * Получаем из этой страницы список приложений
     * @param $link
     * @return mixed
     */
    public function getAppList($link)
    {
        $retry = 0;
        do {
            /* если ответа нет, то обработки ответа не происходит */
            if(!$data = $this->processUrl($link)) {
                Yii::error('[NO DATA RETURNED: '.$link.']','parseInfo');
                return false;
            }
            /* Парсим список приложений из полученного содержимого страницы аккаунта */
            $appList = $this->parseAppList($data);
            /* Это для различной статистики добавляется каунт в апплисте к общему */
            $this->listCount += count($appList);
            $retry++;
            /* Все это повторяется($this->maxRetry раз) до тех пор пока у нас нет ничего
                в списке приложений + с каждым повтором цикла - пишется $this->logErr */
        } while (!count($appList) && $retry < $this->maxRetry && $this->logErr($appList, $retry));
        if(!count($appList)) {
            Yii::error('[GETTING APPLIST FAILED] :
             ---- [THIS MAY HAPPEN DUE TO CAPTCHA PROBLEM ('.$this->maxRetry.' times in a row)] ----
             ---- [THIS MAY HAPPEN IF URL OR ACCOUNT NAME IS WRONG] ----
             [PAGE RETURNED :::]'.$data.']','parseInfo');
            return false;
        }
        return $appList;
    }

    /**
     * Этот метод существует для использования в цикле do{}while(... && ... && $this->method)
     * Он всегда возвращает true, предварительно выслав сообщение в логи и усыпив скрипт на 1 сек.
     * @param $appList
     * @param $retry
     * @return bool
     *
     */
    private function logErr($appList, $retry){
        Yii::info('[Error in AppList ::: '.$this->account->name.' returns '.count($appList).' apps to parse. Retrying('.($this->maxRetry - $retry).')]','parseInfo');
        sleep(1);
        return true;
    }

}