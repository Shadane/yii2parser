<?php

namespace app\components\parser;

use app\components\streamhandle\StreamHandler;
use Yii;
use phpQuery;
use app\models\UrlHelper;

abstract class PQParser extends BaseParser
{
    /**
     * Package - количество пакетов, передаваемых в каждый
     * новый процесс.
     * @var int
     */
    private $package = 5;
    /**
     * Streamer - обработчик и запускатор процессов.
     * @var StreamHandler
     */
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
     * Выставляет количество пакетов в каждом потоке
     * @param $int
     */
    public function setPackage($int){
        if (is_int($int)){
            $this->package = $int;
        }
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

    /**
     * Получает хтмл страницу и создает из нее объект phpQuery для парсинга.
     * @param $html
     * @return \phpQueryObject|\QueryTemplatesParse|\QueryTemplatesSource|\QueryTemplatesSourceQuery
     */
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

    /**
     * Ждет до конца последнего потока
     * Получает данные от селектора потоков.
     * Т.е конечный результат обработки страниц.
     * @return array
     */
    public function getResult(){
        $this->streamer->waitToFinishAll();
        return $this->streamer->getResult();
    }


    /**
     * Для каждого приложения в списке приложений со страницы аккаунта
     * 1) Достаем ссылку на страницу приложения
     * 2) На случай неизвестных символов перекодировываем PATH в этой ссылке
     * 3) Складываем полученный результат в переменную package - пакет данных,
     * который формируется для отправки в новый поток.
     * 4) Если количество ссылок в пакете достигает номера, указанного в пере-
     * менной $this->package, то запускаем поток и обнуляем пакеты.
     * 5) Если пакетов было(к примеру) меньше, чем необходимо, а массив уже закончился,
     * то после цикла они также отправляются на обработку в поток
     * 6) Удаляем ненужные остатки phpQuery documents,
     * 7) Получаем результат от потоков и возвращаем его.
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


    /**
     * Этот метод запускается исключительно из потоков,
     * Для его работы необходимо переслать массив,
     * содержащий один или несколько $url
     *
     * @param $url
     * @param $acc
     * @return array
     */
    public function processPages($url, $acc){

        $this->account = $acc;
        foreach($url as $appUrl) {
            $this->app['url'] = $appUrl;
            $this->processApp();
            $this->appPush($this->app);
        }
        return $this->getApps();
    }


    /**
     * По URL достается страница приложения и парсится, далее проверяются
     * полученные поля, если есть пустые - все повторяется снова(и так до
     * $this->maxRetry раз.
     * @return bool
     */
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