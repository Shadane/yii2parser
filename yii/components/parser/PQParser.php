<?php

namespace app\components\parser;

use Yii;
use phpQuery;
use app\models\UrlHelper;

abstract class PQParser extends BaseParser
{

    /* Следующие константы реализовывать в парсере определенного сайта */

    /* -------------------------------------------------------
     *         Селектор ссылки на следующую страницу
     *           (запускается на странице аккаунта)
     * -------------------------------------------------------
     *            abstract const SELECTOR_NEXTPAGE;
     * ------------------------------------------------------- */



    /**
     * Достает из phpQuery название приложения по селектору
     * @return String
     */
    protected function parseTitle($data)
    {
        return pq($data)->find(static::SELECTOR_TITLE)->text();
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
        return phpQuery::newDocumentHTML($html,'UTF-8');
    }

 /* -----------------------------------------------------------
 *                   nextPageLinkBuild
 * -----------------------------------------------------------
 *  По селектору проверяет есть ли кнопка "Следующая страница",
 *  и если да, то возвращает ссылку на нее, если нет, то
 *  возвращает false
 * ----------------------------------------------------------- */
    protected function getNextPageLink()
    {
        if (!$nextPage = pq(static::SELECTOR_NEXTPAGE)->attr('href')) {
            return false;
        }

        return static::BASE_URL . $nextPage;
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
    protected function processAppList($appList)
    {
        foreach ($appList as $app) {
            phpQuery::unloadDocuments();
            $this->retry = 0;
            do {
                $appLink = $this->parseUrl($app);
                $this->app['url'] = UrlHelper::UrlEncodePath($appLink);
                if (!$data = $this->processUrl($this->app['url'])){
                    continue;
                }

                $this->parseSingleApp($data);
                $this->retry++;
            } while (!$this->checkIntegrity() && $this->retry < 5);
            $this->appPush($this->app);
        }
        return true;
    }

}