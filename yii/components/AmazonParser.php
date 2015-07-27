<?php

namespace app\components;

use phpQuery;

class AmazonParser extends BaseParser
{
    const ACC_LINK = '/s/ref=bl_sr_mobile-apps?_encoding=UTF8&node=2350149011&field-brandtextbin=PLACEHOLDER';
    const BASE_URL = 'www.amazon.com';

    const SELECTOR_TITLE = 'span#btAsinTitle';
    const SELECTOR_DESC = 'div.bucket:has(*:contains("Product Description")) div.content';
    const SELECTOR_PRICE = '.priceLarge';
    const SELECTOR_NEXTPAGE = 'div#pagn a#pagnNextLink';
    const SELECTOR_APPLIST = 'li.s-result-item';
    const SELECTOR_URL_ICON = 'img#main-image';
    const SELECTOR_IMAGES = 'td:has("#main-image-content") script';

    /* -----------------------------------------------------------
     *                   parseImages
     * -----------------------------------------------------------
     *  На амазоне картинки нормальных размеров находятся внутри
     * скрипта в виде json, но при этом сам скрипт еще и с
     * примесями других ненужных нам вещей, поэтому
     * выборку делаем по регулярке.
     * ----------------------------------------------------------- */
    protected function parseImages()
    {
        $scriptWithImages = pq(static::SELECTOR_IMAGES)->text();
        preg_match_all('/(?<="large":)"([^"]+)"/', $scriptWithImages, $match);
        return implode(', ', $match[1]);
    }

}