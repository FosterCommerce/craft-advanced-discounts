<?php

namespace fostercommerce\coupons\migrations;

use craft\db\Migration;
use fostercommerce\coupons\records\Coupon;

class Install extends Migration
{
    public function safeUp(): bool
    {
        $this->createTable(Coupon::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'message' => $this->string()->notNull(),
            'userId' => $this->integer()->notNull(),
            'username' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
        ]);
        $this->createIndex(null, Coupon::TABLE_NAME, ['dateCreated'], false);

        return true;
    }

    public function safeDown(): bool
    {
        if ($this->db->tableExists(Coupon::TABLE_NAME)) {
            $this->dropIndexIfExists(Coupon::TABLE_NAME, ['dateCreated'], false);
            $this->dropTable(Coupon::TABLE_NAME);
        }

        return true;
    }
}
