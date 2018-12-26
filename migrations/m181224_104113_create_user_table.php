<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 */
class m181224_104113_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey()->unsigned(),
            'username' => $this->string(255)->notNull()->unique(),
            'balance' => $this->decimal(9, 2)->defaultValue(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('user');
    }
}
