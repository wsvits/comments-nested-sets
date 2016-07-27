<?php

use yii\db\Migration;

class m160723_150501_comments extends Migration
{
    public function up()
    {
        $this->createTable('comments', [
            'id' => $this->primaryKey(),
            'text' => $this->text()->notNull(),
            'left_key' => $this->integer(10)->notNull(),
            'right_key' => $this->integer(10)->notNull(),
            'depth' => $this->integer(10)->notNull(),
            'parent_id' => $this->integer(10)->notNull(),
            'created' => $this->timestamp()->notNull()->defaultValue(null),
            'updated' => $this->timestamp()->defaultValue(null),
        ]);
    }

    public function down()
    {
        $this->dropTable('comments');
    }
}
