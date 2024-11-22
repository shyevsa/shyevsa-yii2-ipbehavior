<?php

namespace shyevsa\ipbehavior;

use Closure;
use Throwable;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;

/**
 *
 * @property-read null|string|false $createdFrom
 * @property-read null|string|false $updatedFrom
 * @psalm-api
 */
class IpBehavior extends AttributeBehavior
{
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
    public string $format = 'blob';

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
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedFromAttribute
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

        return ($this->format === 'blob') ? self::ip2blob($ip) : $ip;
    }

    protected function getDefaultValue($event)
    {
        if ($this->defaultValue instanceof Closure || (is_array($this->defaultValue) && is_callable($this->defaultValue))) {
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
            $this->_updated_from = ($this->format === 'blob') ? self::blob2ip($this->owner->{$this->updatedFromAttribute}) : $this->owner->{$this->updatedFromAttribute};
        }

        return $this->_updated_from;
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
            $this->_created_from = ($this->format === 'blob') ? self::blob2ip($this->owner->{$this->createdFromAttribute}) : $this->owner->{$this->createdFromAttribute};
        }

        return $this->_created_from;
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function clearAttribute($_event = null): void {
        $this->_created_from = null;
        $this->_updated_from = null;
    }

    /**
     * @param string $ip The IP Address in `string` format
     *
     * @return false|string|null
     */
    public static function ip2blob($ip) {
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
     * @param string $blob The IP Address in `blob`/`binary` format
     *
     * @return false|string|null
     */
    public static function blob2ip($blob)
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
