<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%apple}}`.
 */
class m251204_174432_create_apple_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%apple}}', [
            'id' => $this->primaryKey(),
            'color' => $this->string(32)->notNull(),
            'appeared_at' => $this->integer()->unsigned()->notNull(),
            'fell_at' => $this->integer()->unsigned(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'eaten_percent' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'size' => $this->decimal(3,2)->notNull()->defaultValue(1.00),
            'rotten_at' => $this->integer()->unsigned(),
            'eaten_at' => $this->integer()->unsigned(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%apple}}');
    }
}
