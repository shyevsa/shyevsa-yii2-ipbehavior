<?php

namespace shyevsa\ipbehavior\tests;

use shyevsa\ipbehavior\IpBehavior;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $title
 * @property string $createdFrom
 * @property string $updatedFrom
 * @property string $updated_from
 * @property string $created_from
 * @property string $created_ip
 * @property string $updated_ip
 */
class Post extends ActiveRecord
{
    public static function tableName()
    {
        return 'post';
    }

    public function behaviors()
    {
        return [
            'ipBehavior' => [
                'class' => IpBehavior::class,
                'defaultValue' => '127.0.0.99',
            ],
        ];
    }
}
