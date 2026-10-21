<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%banner}}`.
 */
class m251117_112926_create_banner_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('banners', [
            'id' => $this->primaryKey(),
            'active' => $this->integer()->defaultValue(0),
            'sort' => $this->integer()->defaultValue(0),
            'img_path' => $this->string()->defaultValue(null),
            'name' => $this->string()->defaultValue(null),
            'url' => $this->string()->defaultValue(null),
            'text' => $this->string()->defaultValue(null),
            'time_swap' => $this->integer()->defaultValue(7),
            'user_create' => $this->integer()->defaultValue(null),
            'user_update' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('banners');
    }
}
