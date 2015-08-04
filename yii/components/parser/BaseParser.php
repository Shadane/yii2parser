<?php

namespace app\components\parser;

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
    protected $maxRetry = 10;
    protected $curl;
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
    abstract protected function processAppList($appList);

    /* Метод, в котором будет обрабатываться или каким-то образом доставаться массив приложений */
    abstract protected function getResult();

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


    /** -----------------------------------------------------------
     *                   processAccPage
     * -----------------------------------------------------------
     *  Получает список приложений по переданной ссылке, создает
     * ссылку на следующюю страницу, и запускает обработку
     * полученного списка приложений. Возвращает ссылку
     * на следующую страницу.
     *
     * Если списка приложений не получает, то возвращает false
     * в метод parseByAccount, и парсинг текущего аккаунта
     * завершается.
     * ----------------------------------------------------------- */
    public function processAccPage($link)
    {
        if (!$appList = $this->getApplist($link)){
            return false;
        }
        $this->listCount += count($appList);
        return $appList;
    }

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
        /* также при каждом запросе выставляется новый useragent, точно проверить сложно,
        но после этого перестало кидать на страницу captcha */
        $retry = 0;
        do{
            $retry++;
            $this->curl->setOption(CURLOPT_USERAGENT, 'rtehjghfghfjhgfhgftrr'.mt_rand(0,1231231));
            if ($html = $this->curl->get($link)) {
//                Yii::info('[' . $this->account->name . ',  code: '.$this->curl->responseCode.'] : start processing link: ' . $link, 'parseInfo');
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
            if(!$data = $this->processUrl($link)) {
                return false;
            }
            $appList = $this->parseAppList($data);
            $retry++;
        } while (!$appList && $retry < $this->maxRetry);

        return $appList;
    }


}