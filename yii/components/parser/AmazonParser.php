<?php

namespace app\components\parser;

use phpQuery;

class AmazonParser extends PQParser
{
    const ACC_LINK = '/s/ref=bl_sr_mobile-apps?_encoding=UTF8&node=2350149011&field-brandtextbin=PLACEHOLDER';
    const BASE_URL = 'www.amazon.com';

    const SELECTOR_TITLE = 'span#btAsinTitle';
    const SELECTOR_DESC = '.bucket:has(*:contains("Product Description")) > div:nth-child(2)';
    const SELECTOR_PRICE = '.priceLarge';
    const SELECTOR_NEXTPAGE = 'div#pagn a#pagnNextLink';
    const SELECTOR_APPLIST = 'li.s-result-item';
    const SELECTOR_URL_ICON = 'img#main-image';
    const SELECTOR_IMAGES = 'div#atf-grid-start div > script:nth-child(2)';
    const SELECTOR_PRICE_NOT_AVAILABLE = 'div.no-pricing';

    /** -----------------------------------------------------------
     *                   parseImages
     * -----------------------------------------------------------
     *  На амазоне картинки нормальных размеров находятся внутри
     * скрипта в виде json, но при этом сам скрипт еще и с
     * примесями других ненужных нам вещей, поэтому
     * выборку делаем по регулярке.
     * ----------------------------------------------------------- */
    protected function parseImages($data)
    {
        $scriptWithImages = pq($data)->find(static::SELECTOR_IMAGES)->text();
        preg_match_all('/(?<="large":)"([^"]+)"/', $scriptWithImages, $match);
        return implode(', ', $match[1]);
    }

    /**
     * На амазоне может быть недоступна цене в связи с локацией,
     * в случае когда цены нет запускается второй селектор.
     * @param $data
     * @return string
     */
    protected function parsePrice($data){
        if (!$price = trim(pq($data)->find(static::SELECTOR_PRICE)->text())){
            $price = trim(pq($data)->find(static::SELECTOR_PRICE_NOT_AVAILABLE)->text());
        }
        return $price;
    }

}