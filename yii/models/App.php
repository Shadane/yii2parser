<?php

namespace app\models;

use Yii;
use app\models\Account;
use app\models\Market;

/**
 * This is the model class for table "app".
 *
 * @property integer $id
 * @property integer $market_id
 * @property integer $account_id
 * @property string $title
 * @property string $price
 * @property string $url
 * @property string $url_icon
 * @property string $url_img
 * @property string $description
 */
class App extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'app';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['market_id', 'account_id', 'title', 'price', 'url', 'url_icon', 'url_img', 'description'], 'required'],
            [['market_id', 'account_id'], 'integer'],
            [['description'], 'string'],
            [['title', 'price'], 'string', 'max' => 255],
            [['url', 'url_icon', 'url_img'], 'string', 'max' => 2000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'market_id' => 'Market ID',
            'account_id' => 'Account ID',
            'title' => 'Title',
            'price' => 'Price',
            'url' => 'Url',
            'url_icon' => 'Url Icon',
            'url_img' => 'Url Img',
            'description' => 'Description',
        ];
    }

    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
    }

    public function getMarket()
    {
        return $this->hasOne(Market::className(), ['id' => 'market_id']);
    }

}
