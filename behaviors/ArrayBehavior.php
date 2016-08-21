<?php
namespace weyii\base\behaviors;

use yii\base\Behavior;
use yii\db\BaseActiveRecord;

/**
 * Author: CallMeZ
 * Email: callme-z@qq.com
 * Date: 16/1/29 21:18
 *
 * ```php
 * use weyii\base\behaviors\ArrayBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         'array' => [
 *              'class' => ArrayBehavior::className(),
 *              //'method' => ArrayBehavior::ENCODE_TYPE_SERIALIZE, // 编解码方式 默认serialize
 *              'attributes' => ['attribute'], // 编解码, 常用设置
 *              //'beforeInsertAttributes' => ['attributes'], // 编码 一般不需设置
 *              //'beforeUpdateAttributes' => ['attributes'], // 编码 一般不需设置
 *              //'afterFindAttributes' => ['attributes'] // 解码 一般不设置
 *         ]
 *     ];
 * }
 * ```
 */
class ArrayBehavior extends Behavior
{
    const ENCODE_TYPE_JSON = 'json';
    const ENCODE_TYPE_SERIALIZE = 'serialize';
    /**
     * @var string 编码类型 json和serialize
     */
    public $encodeType = self::ENCODE_TYPE_SERIALIZE;
    /**
     * @var array beforeSave和afterFind编解码的attributes
     */
    public $attributes = [];
    /**
     * @var array beforeInsert编码的attributes
     */
    public $beforeInsertAttributes = [];
    /**
     * @var array beforeUpdate编码的attributes
     */
    public $beforeUpdateAttributes = [];
    /**
     * @var array afterFind解码的attributes
     */
    public $afterFindAttributes = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'evaluateBeforeInsertAttributes',
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'evaluateBeforeUpdateAttributes',
            BaseActiveRecord::EVENT_AFTER_FIND => 'evaluateAfterFindAttributes'
        ];
    }

    /**
     * 数据库BeforeInsert事件中编码数据
     *
     * @param $event
     */
    public function evaluateBeforeInsertAttributes($event)
    {
        $this->evaluateBeforeSaveAttributes($this->attributes + $this->beforeInsertAttributes);
    }

    /**
     * 数据库BeforeUpdate事件中编码数据
     *
     * @param $event
     */
    public function evaluateBeforeUpdateAttributes($event)
    {
        $this->evaluateBeforeSaveAttributes($this->attributes + $this->beforeUpdateAttributes);
    }

    /**
     * 编码数据
     *
     * @param array $attributes
     */
    protected function evaluateBeforeSaveAttributes(array $attributes)
    {
        if (!empty($attributes)) {
            foreach (array_unique($attributes) as $attribute) {
                $value = $this->owner->$attribute;
                if (is_array($value)) {
                    $this->owner->$attribute = $this->encode($value);
                }
            }
        }
    }

    /**
     * AfterFind事件中解码数据
     *
     * @param $event
     */
    public function evaluateAfterFindAttributes($event)
    {
        $attributes = $this->attributes + $this->afterFindAttributes;
        if (!empty($attributes)) {
            foreach (array_unique($attributes) as $attribute) {
                $value = $this->owner->$attribute;
                if (is_string($value)) {
                    $this->owner->$attribute = $this->decode($value);
                }
            }
        }
    }

    /**
     * 编码成字符串
     *
     * @param $value
     * @return string
     */
    protected function encode($value)
    {
        switch($this->encodeType) {
            case self::ENCODE_TYPE_SERIALIZE:
                return serialize($value);
            case self::ENCODE_TYPE_JSON:
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            default:
                return $value;
        }
    }

    /**
     * 解码成数组
     *
     * @param $value
     * @return array
     */
    protected function decode($value)
    {
        switch($this->encodeType) {
            case self::ENCODE_TYPE_SERIALIZE:
                return unserialize($value) ?: [];
            case self::ENCODE_TYPE_JSON:
                return json_decode($value, true) ?: [];
            default:
                return (array)$value;
        }
    }
}