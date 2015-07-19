<?php

namespace app\models;

use Yii;

use app\models\App;
use app\models\Market;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "account".
 *
 * @property integer $id
 * @property string $name
 * @property integer $market_id
 */
class Account extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'market_id'], 'required'],
            [['market_id'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор Аккаунта',
            'name' => 'Название Аккаунта',
            'market_id' => 'Маркет',
        ];
    }

    public function getApps()
    {
        return $this->hasMany(App::className(), ['account_id' => 'id']);
    }
    public function getMarket()
    {
        return $this->hasOne(Market::className(), ['id' => 'market_id']);
    }
    public function getMarketList()
    {
        $droptions = Market::find()->asArray()->all();
        return ArrayHelper::map($droptions, 'id', 'name');
    }
}
