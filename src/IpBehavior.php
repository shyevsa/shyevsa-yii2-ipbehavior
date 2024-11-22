<?php

namespace shyevsa\ipbehavior;

use Closure;
use Throwable;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;

/**
 * IpBehavior automatically fills the specified attribute with the user IP Address
 *
 * To use IpBehavior, insert the following code to your ActiveRecord class:
 *
 * ```php
 * use shyevsa\ipbehavior\IpBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *       IpBehavior::class
 *     ]
 * }
 * ```
 *
 * By default, IpBehavior will fill the `created_from` and `updated_from` attributes
 * with the IP address is read from `Yii::$app->request->userIP` when the record is created and updated.
 *
 * because attribute values will be set automatically by this behavior, they are usually not user input and should therefore
 * not be validated, i.e `created_from` and `updated_from` should not appear in the [[\yii\base\Model::rules()|rules()]] method of the model.
 *
 * For the above implementation to work with MySQL database, please declare the columns(`created_from`, `updated_from`) as varbinary(16).
 *
 * Or if human-readable string is preferable declare the columns(`created_from`, `updated_from`) as varchar(45) and use [[IpBehavior::FORMAT_IP_STRING]] as format.
 * or if the attribute name are different, or you want to use different way to get the IP Address,
 * or you expect the active record will be updated/inserted by console command
 * you may configure the [[createdFromAttribute]], [[updatedFromAttribute]], [[value]], [[defaultValue]] and [[format]] properties like the following:
 *
 * ```php
 * use shyevsa\ipbehavior\IpBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *        'ipBehavior' => [
 *          'class' => IpBehavior::class,
 *          'createdFromAttribute' => 'created_ip',
 *          'updatedFromAttribute' => false,
 *          'defaultValue' => '127.0.0.1',
 *          'value' => function() {
 *             return Yii::$app->request->userHostAddress;
 *           }
 *          'format' => IpBehavior::FORMAT_IP_STRING
 *        ]
 *    ]
 * }
 * ```
 *
 * and then when the need to view the IP Address in `string` format again use the `getUpdatedFrom()` and `getCreatedFrom()` method
 * or `createdFrom` and `updatedFrom` attribute.
 *
 * ```php
 * echo $model->createdFrom;
 * echo $model->getUpdatedFrom();
 * ```
 *
 * or manual input for example from form input is needed
 * use `createdFrom` and `updatedFrom` attribute on [[\yii\base\Model::rules()|rules()]]
 * and disable the automatic fill by setting [[createdFromAttribute]], [[updatedFromAttribute]] to false
 *
 * ```php
 * use shyevsa\ipbehavior\IpBehavior;
 *
 *
 * public function rules()
 * {
 *     return [
 *       [['createdFrom', 'updatedFrom'], 'ip']
 *     ];
 * }
 *
 * public function behaviors()
 *  {
 *      return [
 *         'ipBehavior' => [
 *           'class' => IpBehavior::class,
 *           'createdFromAttribute' => false,
 *           'updatedFromAttribute' => false,
 *         ]
 *     ];
 *  }
 *
 *
 * ```
 *
 * @property null|string|false $createdFrom
 * @property null|string|false $updatedFrom
 * @psalm-api
 */
class IpBehavior extends AttributeBehavior
{
    public const FORMAT_IP_STRING = 'string';
    public const FORMAT_IP_BLOB = 'blob';

    /**
     * @var string the attribute that will receive current IP Address
     * set this to false if you do not want to record the creator IP Address
     */
    public string $createdFromAttribute = 'created_from';

    /**
     * @var string the attribute that will receive current IP Address
     * Set this to false if you do not want to record the updater IP Address
     */
    public string $updatedFromAttribute = 'updated_from';

    /**
     * {@inheritDoc}
     *
     * in case, when the value is `null`, the value of `Yii::$app->request->userIP` will be used
     */
    public $value;

    /**
     * @var mixed Default value in case request is from console and no IP is available
     */
    public $defaultValue;

    /**
     * @var string Format of the IP Address in `blob` or in `string`
     */
    public string $format = self::FORMAT_IP_BLOB;

    /**
     *
     * {@inheritDoc}
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->createdFromAttribute, $this->updatedFromAttribute],
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedFromAttribute,
            ];
        }
    }

    /**
     *
     * {@inheritDoc}
     *
     * @return void
     */
    public function attach($owner)
    {
        parent::attach($owner);

        $this->owner->on(BaseActiveRecord::EVENT_AFTER_REFRESH, [$this, 'clearAttribute']);
    }

    /**
     * {@inheritDoc}
     *
     * In Case, when the [[value]] property is `null`, the value of [[defaultValue]] will be used as the value
     */
    protected function getValue($event)
    {
        if ($this->value === null && isset(Yii::$app->request->userIP)) {
            $ip = Yii::$app->request->userIP;
        } elseif ($this->value === null) {
            $ip = $this->getDefaultValue($event);
        } else {
            $ip = parent::getValue($event);
        }

        return ($this->format === self::FORMAT_IP_BLOB) ? self::ip2blob($ip) : $ip;
    }

    protected function getDefaultValue($event)
    {
        if ($this->defaultValue instanceof Closure || (is_array($this->defaultValue) && is_callable(
                    $this->defaultValue,
                ))) {
            return call_user_func($this->defaultValue, $event);
        }

        return $this->defaultValue;
    }

    private $_updated_from;

    /**
     * Return the IP Address of the user who updated the record in `string` format
     *
     * @return false|string|null
     */
    public function getUpdatedFrom()
    {
        if (!isset($this->_updated_from) && !empty($this->updatedFromAttribute)) {
            $this->_updated_from = ($this->format === self::FORMAT_IP_BLOB) ? self::blob2ip(
                $this->owner->{$this->updatedFromAttribute},
            ) : $this->owner->{$this->updatedFromAttribute};
        }

        return $this->_updated_from;
    }

    /**
     * @param string $updated_from
     * @return \yii\base\Component
     */
    public function setUpdatedFrom($updated_from)
    {
        $this->_updated_from = $updated_from;
        $this->owner->{$this->updatedFromAttribute} = ($this->format === self::FORMAT_IP_BLOB) ? self::ip2blob(
            $this->_updated_from,
        ) : $this->_updated_from;

        return $this->owner;
    }

    private $_created_from;

    /**
     * Return the IP Address of the user who created the record in `string` format
     *
     * @return false|string|null
     */
    public function getCreatedFrom()
    {
        if (!isset($this->_created_from) && !empty($this->createdFromAttribute)) {
            $this->_created_from = ($this->format === self::FORMAT_IP_BLOB) ? self::blob2ip(
                $this->owner->{$this->createdFromAttribute},
            ) : $this->owner->{$this->createdFromAttribute};
        }

        return $this->_created_from;
    }

    /**
     * @param $created_from
     * @return \yii\base\Component
     */
    public function setCreatedFrom($created_from)
    {
        $this->_created_from = $created_from;
        $this->owner->{$this->createdFromAttribute} = ($this->format === self::FORMAT_IP_BLOB) ? self::ip2blob(
            $this->_created_from,
        ) : $this->_created_from;

        return $this->owner;
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function clearAttribute($_event = null): void
    {
        $this->_created_from = null;
        $this->_updated_from = null;
    }

    /**
     * @param string|null $ip The IP Address in `string` format
     *
     * @return false|string|null
     */
    public static function ip2blob(?string $ip)
    {
        if (empty($ip)) {
            return null;
        }

        try {
            return inet_pton($ip);
        } catch (Throwable $e) {
            Yii::error(['msg' => $e->getMessage(), 'ip' => $ip], __METHOD__);
        }

        return null;
    }

    /**
     * @param string|null $blob The IP Address in `blob`/`binary` format
     *
     * @return false|string|null
     */
    public static function blob2ip(?string $blob)
    {
        if (empty($blob)) {
            return null;
        }

        try {
            return inet_ntop($blob);
        } catch (Throwable $e) {
            Yii::error(['msg' => $e->getMessage(), 'blob' => bin2hex($blob)], __METHOD__);
        }

        return null;
    }
}
