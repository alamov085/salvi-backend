<?php

use yii\db\Migration;

class m260625_010001_create_brand extends Migration
{
    public function safeUp()
    {
        $this->createTable('brand', [

            'id' => $this->primaryKey(),

            'uuid' => $this->string()->notNull(),

            'name' => $this->string()->notNull(),

            'code' => $this->string()->notNull()->unique(),

            'vendor_uuid' => $this->string(),

            'created_at' => $this->integer(),

            'updated_at' => $this->integer(),

        ]);
    }

    public function safeDown()
    {
        $this->dropTable('brand');
    }
}