<?php
namespace ZynLang\Html;

use RuntimeException;

class Base {
    const ATTR_TYPE_NORMAL = 'str';
    const ATTR_TYPE_SET = 'set';
    const ATTR_TYPE_DICT = 'dict';

    /** @var string */
    protected $tagName;

    /** @var string[] */
    protected $attributes = [];

    /** @var string */
    protected $html = null;

    const SELF_CLOSABLE_TAGS = [
        'area', 'base', 'br', 'col', 'embed',
        'hr', 'img', 'input', 'link', 'meta',
        'param', 'source', 'track', 'wbr'
    ];

    const ATTR_TYPES = [
        'class' => self::ATTR_TYPE_SET,
        'style' => self::ATTR_TYPE_DICT,
    ];

    public function __construct (string $tagName) {
        if (! $tagName) {
            throw new RuntimeException('Tag name cannot be empty');
        }

        $this->tagName = strtolower($tagName);
    }

    /**
     * Get, set, or alter and attribute
     *
     * @param string $key
     * @param bool|string|string[] $values
     *      If a boolean value is given, a boolean-style attribute will be added or removed.
     *      If a string is given, this value will override any existing value in the attribute.
     *          The merge flag can be used to force merging the new value with the existing value.
     *      If an array is given, merge will be assumed.
     * @param bool $override
     * @return $this
     */
    public function attr(string $key, $values = true, $override = false) {
        if ($values === true) {
            return $this->setAttr($key);
        }

        if ($values === false) {
            return $this->delAttr($key);
        }

        if (! $override && self::getType($key) != self::ATTR_TYPE_NORMAL) {
            return $this->addAttrVal($key, $values);
        }

        return $this->setAttr($key, $values);
    }

    /**
     * Set the value of the given attribute. If no value is provided, a boolean attribute will be used.
     *
     * @param string $key The
     * @param string|null $value
     * @return $this
     */
    public function setAttr(string $key, ?string $value = null) {
        $key = $this->filterAttrKey($key);

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get the attribute value.
     *
     * @param string $key
     * @return string|null
     */
    public function getAttr(string $key) {
        return (array_key_exists($key, $this->attributes) ? $this->attributes[$key] : null);
    }

    /**
     * Remove the given attribute.
     *
     * @param string $key
     * @return $this
     */
    public function delAttr(string $key) {
        unset($this->attributes[$key]);

        return $this;
    }

    /**
     * Update/join attribute with the given value.
     *
     * @param string $key
     * @param string|string[] $newValues
     * @return $this
     */
    public function addAttrVal(string $key, $newValues) {
        $key = $this->filterAttrKey($key);
        $keyType = self::getType($key);

        $curVal = ($this->getAttr($key) ?: '');
        $curValues = $this->processValues($key, $curVal);
        $newValues = $this->processValues($key, $newValues);

        $attributeValue = null;

        if ($keyType == self::ATTR_TYPE_DICT) {
            $curValues = array_replace($curValues, $newValues);
            $styleParts = [];

            foreach ($curValues as $sKey => $sValue) {
                $styleParts[] = $sKey . ': ' . $sValue;
            }

            $attributeValue = join('; ', $styleParts);
        }
        else {
            $toValues = array_unique(array_merge($curValues, $newValues));
            $attributeValue = join(' ', $toValues);
        }

        $this->attributes[$key] = $attributeValue;

        return $this;
    }

    /**
     * Remove the current attribute value with the given value using the separator.
     *
     * @param string $key
     * @param string|string[] $delValues
     * @return $this
     */
    public function delAttrVal(string $key, $delValues) {
        $key = $this->filterAttrKey($key);
        $keyType = self::getType($key);

        $curVal = ($this->getAttr($key) ?: '');
        $curValues = $this->processValues($key, $curVal);

        $attributeValue = null;

        if (! is_array($delValues)) {
            $delValues = [$delValues];
        }

        if ($keyType == self::ATTR_TYPE_DICT) {
            foreach ($delValues as $value) {
                unset($curValues[$value]);
            }

            $styleParts = [];

            foreach ($curValues as $sKey => $sValue) {
                $styleParts[] = $sKey . ': ' . $sValue;
            }

            $attributeValue = join('; ', $styleParts);
        }
        else {
            $toValues = array_diff($curValues, $delValues);
            $attributeValue = join(' ', $toValues);
        }

        $this->attributes[$key] = $attributeValue;

        if (! count($curValues)) {
            $this->delAttr($key);
        }

        return $this;
    }

    /**
     * Replace one attribute value for another
     *
     * @param string $key
     * @param string $oldValue
     * @param string $newValue
     * @return $this
     */
    public function altAttrVal(string $key, string $oldValue, string $newValue) {
        $this->delAttrVal($key, $oldValue);
        $this->addAttrVal($key, $newValue);

        return $this;
    }

    /**
     * Set the inner HTML of the object.
     *
     * @param string|\ZynLang\Html $html
     * @return $this
     */
    public function html($html) {
        $this->html = $html;

        return $this;
    }

    public function __toString () {
        $attributesParts = [];

        /**
         * @var string $key
         * @var string|array $value
         */
        foreach ($this->attributes as $key => $value) {
            if ($value === null) {
                $attributesParts[] = $key;
            }
            else {
                $attributesParts[] = $key . '="' . htmlspecialchars($value, ENT_XHTML | ENT_QUOTES, 'UTF-8', false) . '"';
            }
        }

        $attributesPart = join(' ', $attributesParts);

        $elemStr = $this->tagName . ($attributesPart ? ' ' . $attributesPart : '');

        if ($this->html === null && $this->canSelfClose()) {
            return '<' . $elemStr . ' />';
        }
        else {
            return '<' . $elemStr . '>' . $this->html . '</' . $this->tagName . '>';
        }
    }

    protected function filterAttrKey(string $key) {
        if (! $key) {
            throw new RuntimeException('Attribute key cannot be empty');
        }

        return strtolower($key);
    }

    protected function canSelfClose () {
        return in_array($this->tagName, self::SELF_CLOSABLE_TAGS);
    }

    /**
     * @param string $key
     * @param array|string $valueOrValues
     * @return array|string
     */
    protected function processValues (string $key, $valueOrValues) {
        $type = self::getType($key);

        if (! $valueOrValues) {
            return [];
        }

        $output = [];

        if ($type == self::ATTR_TYPE_SET) {
            if (! is_array($valueOrValues)) {
                $valueOrValues = [$valueOrValues];
            }

            foreach ($valueOrValues as $value) {
                $values = array_filter(explode(' ', $value));
                $output = array_merge($output, $values);
            }

            $output = array_map('trim', $output);
            $output = array_unique($output);

            return $output;
        }

        if ($type == self::ATTR_TYPE_DICT) {
            if (! is_array($valueOrValues)) {
                $valueOrValues = [$valueOrValues];
            }

            foreach ($valueOrValues as $key => $value) {
                if (! is_numeric($key)) {
                    $output[$key] = $value;
                    continue;
                }

                $values = array_filter(explode(';', $value));

                foreach ($values as $v) {
                    $pair = explode(':', $v);

                    if (count($pair) != 2) {
                        continue;
                    }

                    list($dKey, $dValue) = $pair;

                    $output[trim($dKey)] = trim($dValue);
                }
            }

            return $output;
        }

        return $valueOrValues;
    }

    protected static function getType ($key) {
        if (array_key_exists($key, self::ATTR_TYPES)) {
            return self::ATTR_TYPES[$key];
        }

        return self::ATTR_TYPE_NORMAL;
    }
}