<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use phpQuery;
use linslin\yii2\curl;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($author)
    {
        $rawAuthor = rawurlencode($author);
        echo $rawAuthor;
        //Init curl
        $curl = new curl\Curl();
        $curl->setOption(CURLOPT_FOLLOWLOCATION, true);

        $link = 'www.amazon.com/s/ref=bl_sr_mobile-apps?_encoding=UTF8&field-brandtextbin='.$rawAuthor.'&node=2350149011';
        do
        {
            $html = $curl->get($link);
            echo $curl->responseCode;
            switch ($curl->responseCode) {
                case 200:
                    $apps = [];
                    phpQuery::newDocument($html);

                   echo 'next page is:'. $nextPage = pq('div#pagn a#pagnNextLink')->attr('href');

                    foreach (pq('li.s-result-item') as $app) {
                        $appLink = pq($app)->find('a')->attr('href');
                        $appPage = $curl->get($appLink);

                        switch ($curl->responseCode) {
                            case 200:
                                $app = [];
                                $appPage = phpQuery::newDocument($appPage);
                                $appName = pq($appPage)->find('span#btAsinTitle')->text();

                                $app['title'] = $appName;
                                $app['description'] = pq($appPage)->find('h2:contains("Product Description")')->next('div.content')->text();
                                $app['price'] = trim(pq($appPage)->find('.priceLarge')->text());
                                $app['url'] = $appLink;
                                $app['url_icon'] = pq($appPage)->find('img#main-image')->attr('src');

                                $scriptWithImages = pq('td:has("#main-image-content") script')->text();
                                preg_match('/(?={).*(?=;)/', $scriptWithImages, $match);
                                
                                if (isset($match[0])) {
                                    $app['url_img'] = $match[0];
                                }

                                $app['market'] = 'amazon';
                                $app['account'] = $rawAuthor;
                                break;
                            default:
                                break;
                        }
                        $apps[] = $app;
                    }
                    print_r($apps);
                    break;

                case 404:
                    //404 Error logic here
                    break;
            }
            $link = 'www.amazon.com'.$nextPage;
            echo "\n";
            print_r($link);
            echo 'page end' . "\n";
        } while ($link !== 'www.amazon.com');
    }
}
