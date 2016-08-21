<?php
use yii\helpers\Html;

if (! function_exists('e')) {
    /**
     * Escape HTML entities in a string.
     *
     * @param $value
     * @param bool|true $doubleEncode
     * @return string
     */
    function e($value, $doubleEncode = true)
    {
        return Html::encode($value, $doubleEncode);
    }
}

if (! function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param  string  $trait
     * @return array
     */
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
