<?php

namespace shyevsa\ipbehavior\tests;

use shyevsa\ipbehavior\IpBehavior;

class PostManual extends Post
{
    public function rules()
    {
        return [
            [['title'], 'string', 'max' => 255],
            [['createdFrom', 'updatedFrom'], 'ip'],
        ];
    }

    public function behaviors()
    {
        return [
            'ipBehavior' => [
                'class' => IpBehavior::class,
                'skipAutoFill' => true,
                'preserveNonEmptyValues' => true,
                'defaultValue' => '127.0.0.99',
            ],
        ];
    }
}
