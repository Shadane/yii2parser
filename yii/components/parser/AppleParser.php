<?php

namespace app\components\parser;
use Yii;

class AppleParser extends JsonParser
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


    /**
     *  Достает ссылки на картинки по селектору
     * @param $app - элемент списка приложений в ответе api apple
     * @return string
     */
    protected function parseImages($app)
    {
        return implode(',', $app->{static::SELECTOR_IMAGES});
    }

    /**
     * В API apple нет страниц, засим возвращаем false.
     * @return bool
     */
    public function getNextPageLink()
    {
        return false;
    }

    public function getResult(){
        return $this->getApps();
    }







}
