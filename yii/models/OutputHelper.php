<?php
namespace app\models;

use kartik\helpers\Html;
use Yii;
use yii\helpers\ArrayHelper;
use yii\db\Query;

class OutputHelper
{
    /**
     * На взоде получает название таблицы в базе данных,
     * на выходе - отдает отмаппленный массив id=>name.
     * @param $modelName
     * @return array
     */
    public static function mapModelList($modelName){
        $modelList = (new Query())
            ->select('id, name')
            ->from($modelName)
            ->all();
        return ArrayHelper::map($modelList,'id','name');
    }

    /**
     * @param $description - текст(+html), над которым происходят манипуляции.
     * @param $textWrapperStart - Обертка вокруг текста.
     * @param $textWrapperEnd - Закрытие тега обертки.
     * @return string - Возвращает нам html для вывода на экран
     */
    public static function formatDescription($description, $textWrapperStart, $textWrapperEnd){
       return Html::well(
              $textWrapperStart
            . Html::decode(Yii::$app->formatter->asParagraphs($description), Html::SIZE_TINY)
            . $textWrapperEnd);
    }

    /**
     * @param $price
     *
     * цена в виде строки, приводится к нижнему регистру
     * и переводится с помощью Yii::t().На данном этапе
     * переводит только Free и '0' к 'Бесплатно'.
     *
     * @return string
     *
     * Возвращает отформатированную строку.
     */
    public static function formatPrice($price) {
        return Yii::t('app', 'Price') . ': ' . Html::bsLabel(Yii::t('app', mb_strtolower($price, 'UTF-8')));
    }


    /**
     * @param $imgUrl
     * @return array|void
     *
     * На входе - либо строка с ссылками на фотографии через запятую,
     * либо массив. Если строка, то разбиваем ее в массив, а затем
     * прогоняем по каждому элементу и получаем на выходе готовый
     * для вставляния в карусель массив.
     */
    public static function urlsToCarousel($imgUrl){
        $images = [];
        if (!$imgUrl) return;
        $imgUrl = is_string($imgUrl)? explode(',', $imgUrl) : $imgUrl;
        foreach ($imgUrl as $url) {
            $images[] = Html::img($url);
        }
        return $images;
    }
}