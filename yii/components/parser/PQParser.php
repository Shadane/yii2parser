<?php

namespace app\components\parser;

use app\components\streamhandle\StreamHandler;
use Yii;
use phpQuery;
use app\models\UrlHelper;

abstract class PQParser extends BaseParser
{
    private $package = 1;
    private $streamer;

    /* Следующие константы реализовывать в парсере определенного сайта */

    /* -------------------------------------------------------
     *         Селектор ссылки на следующую страницу
     *           (запускается на странице аккаунта)
     * -------------------------------------------------------
     *            abstract const SELECTOR_NEXTPAGE;
     * ------------------------------------------------------- */

    public function __construct(){
        parent::__construct();
        $this->streamer = new StreamHandler();
    }


    /**
     * Достает из phpQuery название приложения по селектору
     * @return String
     */
    protected function parseTitle($data)
    {
        return pq($data)->find(static::SELECTOR_TITLE)->text();
    }

    public function getStreamer(){
        return $this->streamer;
    }

    /**
     * Достает из phpQuery описание приложения по селектору
     * @return String
     */
    protected function parseDescription($data)
    {
        return trim(pq($data)->find(static::SELECTOR_DESC)->html());
    }

    /**
     * Достает из phpQuery стоимость приложения по селектору
     * @return String
     */
    protected function parsePrice($data)
    {
        return trim(pq($data)->find(static::SELECTOR_PRICE)->text());
    }

    /**
     * Достает из phpQuery иконку приложения по селектору
     * @return String
     */
    protected function parseUrlIcon($data)
    {
        return pq($data)->find(static::SELECTOR_URL_ICON)->attr('src');
    }

    /**
     * Достает из phpQuery список приложений по селектору
     * @return String
     */
    protected function parseAppList($data)
    {
        return pq($data)->find(static::SELECTOR_APPLIST);
    }

    /**
     * Достает из phpQuery Url по селектору
     * @return String
     */
    protected function parseUrl($app)
    {
        return pq($app)->find('a')->attr('href');
    }

    protected function responseDecode($html)
    {
        return phpQuery::newDocumentHTML($html, 'utf-8');
    }

 /* -----------------------------------------------------------
 *                   nextPageLinkBuild
 * -----------------------------------------------------------
 *  По селектору проверяет есть ли кнопка "Следующая страница",
 *  и если да, то возвращает ссылку на нее, если нет, то
 *  возвращает false
 * ----------------------------------------------------------- */
    public function getNextPageLink()
    {
        if (!$nextPage = pq(static::SELECTOR_NEXTPAGE)->attr('href')) {
            return false;
        }

        return static::BASE_URL . $nextPage;
    }

    public function getResult(){
        $this->streamer->waitToFinishAll();
        return $this->streamer->getResult();
    }


    /**
     * Для каждого приложения в списке приложений со страницы аккаунта
     * 1) Достаем ссылку на страницу приложения
     * 2) На случай неизвестных символов перекодировываем PATH в этой ссылке
     * 3) Получаем декодированный результат по переходу по этой ссылке
     * 4) Обрабатываем результат в методе parseSingleApp(где и парсятся отдельные поля)
     * 5) Проверяем полученные поля на пустоту, если есть пустые, то пробуем еще до 5 раз
     * 6) Добавляем проверенное приложение в массив приложений
     * @param $appList
     * @return bool
     */
    public function processAppList($appList)
    {
        $package=[];
        foreach ($appList as $app) {
            $appUrl = $this->parseUrl($app);
            $package[] = UrlHelper::UrlEncodePath($appUrl);
            if (   (count($package) === $this->package)){
                $this->streamer->runStream($package, $this->account->id);
                $package = [];
            }
        }
        if($package){
            $this->streamer->runStream($package, $this->account->id);
        }
        phpQuery::unloadDocuments();
        return $this->streamer->getResult();
    }



    public function processPage($url, $acc, $time){

        $this->account = $acc;
        $urlArr = explode(',',$url);
        foreach($urlArr as $appUrl) {
            $this->app['url'] = $appUrl;
//        $timeTwo = explode(' ', microtime());
//        $timeTwo = $timeTwo[0]+$timeTwo[1];
//        $this->app['timeBeforeRequest'] = $timeTwo - $time ;
            $this->processApp();
            $this->appPush($this->app);
        }
        return $this->getApps();
    }

    protected function processApp(){
        $retry=0;
        do {
            if (!$data = $this->processUrl($this->app['url'])){
                return false;
                }
            $this->parseSingleApp($data);
            $retry++;
        } while (!$this->checkIntegrity($retry) && $retry < $this->maxRetry);
        return true;
    }

}