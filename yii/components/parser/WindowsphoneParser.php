<?php

namespace app\components\parser;

use phpQuery;

class WindowsphoneParser extends PQParser
{
    const ACC_LINK = '/ru-RU/store/publishers?publisherId=PLACEHOLDER';
    const BASE_URL = 'www.windowsphone.com';

    const SELECTOR_TITLE = 'h1[itemprop="name"]';
    const SELECTOR_DESC = 'pre[itemprop="description"]';
    const SELECTOR_PRICE = 'span[itemprop="price"]';
    const SELECTOR_NEXTPAGE = 'a#nextLink';
    const SELECTOR_APPLIST = 'td:has(a[data-os="app"])';
    const SELECTOR_URL_ICON = 'img.appImage';
    const SELECTOR_IMAGES = 'a:has(img[itemprop="screenshot"])';


    /** -----------------------------------------------------------
     *                   parseImages
     * -----------------------------------------------------------
     *  По селектору находим картинки и возвращаем их запросчику
     * ----------------------------------------------------------- */
    protected function parseImages($data)
    {
        $images = [];
        foreach (pq($data)->find(static::SELECTOR_IMAGES) as $img)
        {
            $images[] = pq($img)->attr('href');
        }
        return implode(', ', $images);
    }
}

