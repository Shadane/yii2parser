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
    protected $retry = 0;
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
    abstract protected function getNextPageLink();

    /* Абстрактный метод, в котором будет декодироваться ответ, полученный в результате curl запроса */
    abstract protected function responseDecode($response);

    /* В этом методе будет обрабатываться список приложений с аккаунта на сайте */
    abstract protected function processAppList($appList);

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

    /* Создается объект MyCurl и прописываются настройки */
    protected function curlInit()
    {
        $this->curl = new MyCurl();
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true);
    }


    /** --------------------------------------------------------
     *                     parseByAccount
     * --------------------------------------------------------
     * Записываем аккаунт в свойство объекта, затем получаем
     * ссылку для парсинга методом getLink(), и запускаем
     * cUrl. Дальше обрабатываем полученую страницу,
     * и возвращаем поля полученных приложений.
     * -------------------------------------------------------- */
    public function parseByAccount($account)
    {
        $this->account = $account;
        $link = $this->getLink();
        do {
            /*  Запускаем обработку страницы аккаунту. processAccPage возвращает
                 ссылку на следующую страницу( либо ссылку либо false в $link)    */
            $link = $this->processAccPage($link);

            /* do while будет выполняться до тех пор пока создается новая ссылка из метода processAccPage */
        } while ($link);
        return $this->getApps();
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


    /** --------------------------------------------------------
     *                     getLink
     * --------------------------------------------------------
     * Соединяет константы с сылками в нужном нам порядке и
     * меняет PLACEHOLDER на название аккаунта.
     * -------------------------------------------------------- */
    protected function getLink()
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
     * ----------------------------------------------------------- */
    protected function processAccPage($link)
    {
        $appList = $this->getApplist($link);
        $link = $this->getNextPageLink();
        $this->processAppList($appList);

        return $link;
    }

    /**
     * Проверяет поля, полученные при парсинге,
     * если среди них есть пустые, то во-
     * звращает false, иначе true.
     * @return bool
     */
    protected function checkIntegrity()
    {
        foreach ($this->app as $fieldName => $field) {
            if (!$field) {
                Yii::info('Parsing failure: ' . $fieldName . ' not Found. ' . (5 - $this->retry) . ' retries left. Retrying in 2 seconds', 'parseInfo');
                sleep(2);
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
        $this->curl->setOption(CURLOPT_USERAGENT, 'sdfsdfsd'.mt_rand(0,1231231));

        if ($html = $this->curl->get($link)) {
//            Yii::info(($html),'parseInfo');
            Yii::info('[' . $this->account->name . ',  code: '.$this->curl->responseCode.'] : start processing link: ' . $link, 'parseInfo');
            return $this->responseDecode($html);
        } else {
            Yii::info('[' . $this->account->name . '] : ERROR processing link: ' . $link, 'parseInfo');
        }
    }

    /**
     * Получаем по ссылке страницу с сервера
     * Получаем из этой страницы список приложений
     * @param $link
     * @return mixed
     */
    private function getAppList($link)
    {
        $this->retry = 0;
        do {
            $data = $this->processUrl($link);
            $appList = $this->parseAppList($data);
            $this->retry++;
        } while (!$appList && $this->retry < 5);

        return $appList;
    }


}