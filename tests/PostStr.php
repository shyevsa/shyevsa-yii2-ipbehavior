<?php

namespace shyevsa\ipbehavior\tests;

use shyevsa\ipbehavior\IpBehavior;

class PostStr extends Post
{
    public function behaviors()
    {
        return [
            'ipBehavior' => [
                'class' => IpBehavior::class,
                'createdFromAttribute' => 'created_ip',
                'updatedFromAttribute' => 'updated_ip',
                'format' => IpBehavior::FORMAT_IP_STRING,
                'defaultValue' => '127.0.0.99',
            ],
        ];
    }
}
