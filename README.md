# Yii2 IP Behavior

Yii2 Behavior for updating IP address on update or insert
with ip address in `blob` format or `string` format.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/).

```bash
composer require shyevsa/yii2-ipbehavior "*"
```

or add

```txt
"shyevsa/yii2-ipbehavior": "*"
```

to the `require` section of your `composer.json`.

## Usage

### Default Blob Format

By Default the behavior will use `blob` format ip address for `created_from` and `updated_from` attributes.
this required the database column to be set as `binary` or `varbinary` type.

```php
<?php

use yii\db\Migration;

/**
 * Class m241122_064211_post
 */
class m241122_064211_post extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%post}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text()->notNull(),
            'created_from' => 'varbinary(16) null', // or $this->binary(16)->null(),
            'updated_from' => 'varbinary(16) null', // or $this->binary(16)->null(),
        ]);
    }
}
````

Attach the behavior to your model class.

```php
use shyevsa\ipbehavior\IpBehavior;

class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'ipBehavior' => [
                'class' => IpBehavior::class,
                'createdFromAttribute' => 'created_from', // the attribute name of the creator IP address
                'updatedFromAttribute' => 'updated_from', // set to `false` if you don't need to update the IP address
            ]
        ];
    }
}
```
and then if you need to display the IP Address you can use the `getCreatedFrom` and `getUpdatedFrom` methods.

```php
echo $model->getCreatedFrom();
echo $model->getUpdatedFrom();

// or

echo $model->createdFrom;
echo $model->updatedFrom;
```

### String Format

if you prefer to use `string` format ip address for `created_from` and `updated_from` attributes.

```php
use shyevsa\ipbehavior\IpBehavior;

class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'ipBehavior' => [
                'class' => IpBehavior::class,
                'format' => IpBehavior::FORMAT_IP_STRING
            ]
        ];
    }
}
```

### When Yii::$app->request->userIP is not available

if the active record is expected to be used on console command, 
you may configure the [[defaultValue]] to static IP address.

```php
use shyevsa\ipbehavior\IpBehavior;

public function behaviors()
{
    return [
        'ipBehavior' => [
            'class' => IpBehavior::class,
            'defaultValue' => '127.0.0.1'
        ]
    ];
}
```

### Different attribute name

if the attribute name are different, or you want to use different way to get the IP Address,
you can configure the [[createdFromAttribute]], [[updatedFromAttribute]] and [[value]] properties.

```php
use shyevsa\ipbehavior\IpBehavior;

public function behaviors() {
    return [
        'ipBehavior' => [
            'class' => IpBehavior::class,
            'createdFromAttribute' => 'created_ip',
            'updatedFromAttribute' => 'updated_ip',
            'value' => function () {
                return Yii::$app->request->remoteIP;
            }
        ]
    ];
}
```

### Manual Input

If you want to manually input the IP address, from form input for example.
you can use the [[createdFrom]] and [[updatedFrom]] attribute on [[\yii\base\Model::rules()|rules()]]
and set the [[preserveNonEmptyValues]] to `true`.
or disable automatic fill by setting the [[skipAutoFill]] to `true` or callable that returns `true` for fully manual mode.

```php
use shyevsa\ipbehavior\IpBehavior;

public function rules()
{
 return [
   [['createdFrom', 'updatedFrom'], 'ip'],
 ];
}

public function behaviors()
{
  return [
     'ipBehavior' => [
       'class' => IpBehavior::class,
       'preserveNonEmptyValues' => true,
       // For fully manual mode
       // 'skipAutoFill' => false,
     ],
  ];
}

```

**Note**: 

When [[preserveNonEmptyValues]] is `true` and [[updatedFromAttribute]] is not empty, 
[[updatedFromAttribute]] will not automatically update, 
to make it work again set [[updatedFromAttribute]] to empty string 
before calling [[yii\db\ActiveRecord::save()|save()]] or [[yii\db\ActiveRecord::update()|update()]].
