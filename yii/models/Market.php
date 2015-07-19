<?php

namespace app\models;

use Yii;

use app\models\App;
/**
 * This is the model class for table "market".
 *
 * @property integer $id
 * @property string $name
 */
class Market extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'market';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    public function getApps()
    {
        return $this->hasMany(App::className(), ['market_id' => 'id']);
    }

    public static function findIdByName($marketName)
    {
        $market = static::findOne(['name'=> $marketName]);
        return $market ? $market->id : false;
    }
}
