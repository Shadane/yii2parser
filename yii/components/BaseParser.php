<?php

namespace app\components;

use Yii;
use app\components\MyCurl;
use phpQuery;

abstract class BaseParser
{
    /* --------------------------------------------------
     * Переменные $curl и $account тут для того, чтобы
     * не приходилось их передавать между методами
     * -------------------------------------------------- */
    protected $curl;
    protected $account;

    /* --------------------------------------------------
     * Переменная $apps содержит в себе готовые массивы
     * приложений, которые в дальнейшем можно достать
     * и сохранить в базу данных.
     * -------------------------------------------------- */
    protected $apps = [];

    public function __construct()
    {
        $this->curlInit();
    }


    protected function appPush($app)
    {
        $this->apps[] = $app;
    }

    /* --------------------------------------------------------
     *                     parseByAccount
     * --------------------------------------------------------
     * Записываем аккаунт в свойство объекта, затем получаем
     * ссылку для парсинга методом getLink(), и запускаем
     * cUrl. Дальше обрабатываем полученую страницу.
     * -------------------------------------------------------- */
    public function parseByAccount($account)
    {
        $this->account = $account;
        $link = $this->getLink();
        echo $link;
        do {
            $html = $this->curl->get($link);

            /*  Запускаем обработку страницы аккаунту. processAccPage возвращает
                 ссылку на следующую страницу( либо ссылку либо false в $link)    */
            $link = $this->processAccPage($html);
            echo "\n".'next page is: ' . $link;
            /* do while будет выполняться до тех пор пока создается новая ссылка из метода processAccPage */
        } while ($link);
        return $this->getApps();
    }

    /* --------------------------------------------------------
     *                     parseSingleApp
     * --------------------------------------------------------
     * Прогоняем нашу полученную от cUrl страницу по селекторам
     * и записываем значения в переменную, далее добавляем
     * полученное приложение в свойство $apps.
     * -------------------------------------------------------- */
    private function parseSingleApp($appHtml, $appLink)
    {
        $app = [];
        phpQuery::newDocument($appHtml);

        echo "\n".'processing: '.$app['title'] = pq(static::SELECTOR_TITLE)->text();
        $app['description'] =trim(pq(static::SELECTOR_DESC)->contents());
        $app['price'] = trim(pq(static::SELECTOR_PRICE)->text());
        $app['url'] = $appLink;
        $app['url_icon'] = pq(static::SELECTOR_URL_ICON)->attr('src');
        $app['url_img'] = $this->parseImages();
        $app['market_id'] = $this->account->market_id;
        $app['account_id'] = $this->account->id;

        $this->appPush($app);
        Yii::info('grabbed app: '.$app['title'], 'parseInfo');
    }

    protected function getApps()
    {
        return $this->apps;
    }

    /* --------------------------------------------------------
     *                     parseSingleApp
     * --------------------------------------------------------
     * Соединяет константы с сылками в нужном нам порядке и
     * меняет PLACEHOLDER на название аккаунта.
     * -------------------------------------------------------- */
    protected function getLink()
    {
        return static::BASE_URL . str_replace('PLACEHOLDER', rawurlencode($this->account->name) ,static::ACC_LINK);
    }

    protected function curlInit()
    {
        $this->curl = new MyCurl();
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true);
    }

    /* -----------------------------------------------------------
     *                   nextPageLinkBuild
     * -----------------------------------------------------------
     *  По селектору проверяет есть ли кнопка "Следующая страница",
     *  и если да, то возвращает ссылку на нее, если нет, то
     *  возвращает false
     * ----------------------------------------------------------- */
    protected function nextPageLinkBuild()
    {
        if (!$nextPage = pq(static::SELECTOR_NEXTPAGE)->attr('href')) {
            return false;
        }

        return static::BASE_URL . $nextPage;
    }

    /* -----------------------------------------------------------
     *                   processAccPage
     * -----------------------------------------------------------
     *  Прогоняет страницу аккаунта по селектору приложений, и на
     * каждое найденное приложение находит его ссылку и юзает
     * cUrl для получения страницы с приложением.
     * ----------------------------------------------------------- */
    protected function processAccPage($html)
    {
        phpQuery::newDocument($html);
        $link = $this->nextPageLinkBuild();
        foreach (pq(static::SELECTOR_APPLIST) as $app) {
            $appLink = pq($app)->find('a')->attr('href');

            if ($appHtml = $this->curl->get($appLink)) {
                $this->parseSingleApp($appHtml, $appLink);
            }
        }
        return $link;
    }

}