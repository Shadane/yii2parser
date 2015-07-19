<?php

namespace app\components;

use phpQuery;

class WindowsphoneParser extends BaseParser
{
    const ACC_LINK = '/en-US/store/publishers?publisherId=PLACEHOLDER';
    const BASE_URL = 'www.windowsphone.com';

    const SELECTOR_TITLE = 'h1[itemprop="name"]';
    const SELECTOR_DESC = 'pre[itemprop="description"]';
    const SELECTOR_PRICE = 'span[itemprop="price"]';
    const SELECTOR_NEXTPAGE = 'a#nextLink';
    const SELECTOR_APPLIST = 'td:has(a[data-os="app"])';
    const SELECTOR_URL_ICON = 'img.appImage';
    const SELECTOR_IMAGES = 'li.R0 a';



    protected function parseImages()
    {
        foreach (pq(static::SELECTOR_IMAGES) as $imageLi)
        {
            $images[] = pq($imageLi)->attr('href');
        }
        return implode(', ', $images);
    }
}

