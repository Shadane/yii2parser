<?php

namespace app\components;


class AppleParser extends BaseParser
{
    const ACC_LINK = '/search?term=PLACEHOLDER&entity=software';
    const BASE_URL = 'itunes.apple.com';

    const SELECTOR_TITLE = 'trackCensoredName';
    const SELECTOR_DESC = 'description';
    const SELECTOR_PRICE = 'formattedPrice';
    const SELECTOR_APP_URL = 'trackViewUrl';

    const SELECTOR_APPLIST = 'results';
    const SELECTOR_URL_ICON = 'artworkUrl100';
    const SELECTOR_IMAGES = 'screenshotUrls';

    /** -----------------------------------------------------------
     *                   processAccPage
     * -----------------------------------------------------------
     * Действует по аналогии с одноименным методом базового класса
     * ----------------------------------------------------------- */
    private function parseSingleApp($appJsonDecoded)
    {
        $app = [];
        $app['title'] = $appJsonDecoded->{static::SELECTOR_TITLE};
        $app['description'] = $appJsonDecoded->{static::SELECTOR_DESC};
        $app['price'] = (string)$appJsonDecoded->{static::SELECTOR_PRICE};
        $app['url'] = $appJsonDecoded->{static::SELECTOR_APP_URL};
        $app['url_icon'] = $appJsonDecoded->{static::SELECTOR_URL_ICON};
        $app['url_img'] = implode(',', $appJsonDecoded->{static::SELECTOR_IMAGES});
        $app['market_id'] = $this->account->market_id;
        $app['account_id'] = $this->account->id;

        $this->appPush($app);
    }
    /** -----------------------------------------------------------
     *                   processAccPage
     * -----------------------------------------------------------
     *  Т.к ответ от api может содержать и другие компании, то
     * нужно фильтровать записи по названию аккаунта, при-
     * ложения от нужной нам компании отправляются на
     * обработку методом parseSingleApp
     * ----------------------------------------------------------- */
    protected function processAccPage($response)
    {
        $data = json_decode($response);
        foreach($data->{static::SELECTOR_APPLIST} as $app){
            if ($app->artistName !== $this->account->name)
            {
                continue;
            }
            $this->parseSingleApp($app);

        }
    }

    protected function nextPageLinkBuild()
    {
            return false;
    }


}
