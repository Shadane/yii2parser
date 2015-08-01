<?php
namespace app\components\parser;
use Yii;

abstract class JsonParser extends \app\components\parser\BaseParser
{
    protected function parseSingleApp($data)
    {
        parent::parseSingleApp($data);
        $this->app['url'] = $this->parseUrl($data);
    }


    /**
     * Достает название приложения по селектору
     * @param $app - элемент списка приложений в ответе api apple
     * @return String
     */
    protected function parseTitle($app)
    {
        return $app->{static::SELECTOR_TITLE};
    }

    /**
     * Достает описание приложения по селектору
     * @param $app - элемент списка приложений в ответе api apple.
     * @return String
     */
    protected function parseDescription($app)
    {
        return trim($app->{static::SELECTOR_DESC});
    }

    /**
     * Достает стоимость приложения по селектору
     * @param $app - элемент списка приложений в ответе api apple
     * @return String
     */
    protected function parsePrice($app)
    {
        return trim($app->{static::SELECTOR_PRICE});
    }

    /**
     * Достает иконку приложения по селектору
     * @param $app - элемент списка приложений в ответе api apple
     * @return String
     */
    protected function parseUrlIcon($app)
    {
        return $app->{static::SELECTOR_URL_ICON};
    }

    /**
     * Декодирование в случае с JSON ответом происходит через JSON_DECODE
     * @param $html
     * @return mixed
     */
    protected function responseDecode($html)
    {
        return json_decode($html);
    }

    /**
     * Достает иконку приложения по селектору
     * @param $app - элемент списка приложений в ответе api apple
     * @return String
     */
    protected function parseUrl($app)
    {
        return $app->{static::SELECTOR_APP_URL};
    }

    /**
     * Достает иконку приложения по селектору
     * @param $data - декодированный ответ от api
     * @return String
     */
    protected function parseAppList($data)
    {
        return $data->{static::SELECTOR_APPLIST};
    }

    /**
     * Для каждого приложения из полученного списка:
     * 1) Проверяем совпадение имени автора приложения с названием аккаунта,
     * это необходимо потому, что могут прийти и некоторые левые результаты.
     * 2) Парсим поля в свойство $app;
     * 3) Проверяем полученные поля на пустоту, если есть пустые, то повторяем парсинг до 5 раз
     * 4) Добавляем полученные поля в массив с отпарсенными массивами приложений.
     * @param $appList
     * @return bool
     */
    protected function processAppList($appList)
    {
        foreach ($appList as $app) {
            if ($app->artistName !== $this->account->name) {
                continue;
            }
            $retry = 0;
            do {
                $this->parseSingleApp($app);
                $retry++;
            } while (!$this->checkIntegrity($retry) && $retry < $this->maxRetry);
            $this->appPush($this->app);

        }
        return true;
    }

}