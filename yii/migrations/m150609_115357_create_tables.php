<?php

use yii\db\Schema;
use yii\db\Migration;

class m150609_115357_create_tables extends Migration
{
//    public function up()
//    {
//
//    }
//
//    public function down()
//    {
//        echo "m150609_115357_create_tables cannot be reverted.\n";
//
//        return false;
//    }
    

//     Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%market}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
        ], $tableOptions);
        $this->createTable('{{%account}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'market_id' => Schema::TYPE_SMALLINT . ' unsigned NOT NULL'
        ], $tableOptions);
//        $this->createIndex('name', '{{%category}}', 'name', true);
        $this->createTable('{{%app}}', [
            'id' => Schema::TYPE_PK,
            'market_id' => Schema::TYPE_SMALLINT . ' unsigned NOT NULL',
            'account_id' => Schema::TYPE_INTEGER . ' unsigned NOT NULL',
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'price' => Schema::TYPE_STRING . ' NOT NULL',
            'url' => 'varchar(2000)' . ' NOT NULL',
            'url_icon' => 'varchar(2000)' . ' NOT NULL',
            'url_img' => 'varchar(60535)' . ' NOT NULL',
            'description' => Schema::TYPE_TEXT . ' NOT NULL'
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%market}}');
        $this->dropTable('{{%account}}');
        $this->dropTable('{{%app}}');
    }

}
