<?php

namespace app\components;

use Yii;
use linslin\yii2\curl\Curl;
use phpQuery;

abstract class BaseParser
{
    protected $curl;
    protected $account;
    protected $apps = [];

    public function __construct()
    {
        $this->curlInit();
    }


    protected function appPush($app)
    {
        $this->apps[] = $app;
    }

    public function parseByAccount($account)
    {
        $this->account = $account;
        $link = $this->getLink($account);
        do {
            if (!$html = $this->curl->get($link)) {
                echo '\n an error occured while getting content.\n';
                break;
            }
            $this->processAccPage($html);
            $link = $this->nextPageLinkBuild();
            echo 'next page is' . $link;
        } while ($link);
        return $this->getApps();
    }

    private function parseSingleApp($appHtml, $appLink)
    {
        $app = [];
        phpQuery::newDocument($appHtml);

        $app['title'] = pq(static::SELECTOR_TITLE)->text();
        $app['description'] = trim(pq(static::SELECTOR_DESC)->text());
        $app['price'] = trim(pq(static::SELECTOR_PRICE)->text());
        $app['url'] = $appLink;
        $app['url_icon'] = pq(static::SELECTOR_URL_ICON)->attr('src');
        $app['url_img'] = $this->parseImages();
        $app['market_id'] = $this->account->market_id;
        $app['account_id'] = $this->account->id;

        $this->appPush($app);
    }

    protected function getApps()
    {
        return $this->apps;
    }

    protected function getLink($account)
    {
        return static::BASE_URL . str_replace('PLACEHOLDER', rawurlencode($account->name) ,static::ACC_LINK);
    }

    protected function curlInit()
    {
        $this->curl = new Curl();
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true);
    }

    protected function nextPageLinkBuild()
    {
        if (!$nextPage = pq(static::SELECTOR_NEXTPAGE)->attr('href')) {
            return false;
        }

        return static::BASE_URL . $nextPage;
    }

    protected function processAccPage($html)
    {
        phpQuery::newDocument($html);
        foreach (pq(static::SELECTOR_APPLIST) as $app) {
            $appLink = pq($app)->find('a')->attr('href');

            if ($appHtml = $this->curl->get($appLink)) {
                $this->parseSingleApp($appHtml, $appLink);
            }
        }
    }

}