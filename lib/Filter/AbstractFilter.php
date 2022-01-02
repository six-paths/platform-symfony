<?php

namespace Sixpaths\Platform\Symfony\Filter;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractFilter
{
    public const DEFAULT_DATAGROUP = 'list';
    public const DEFAULT_LIMIT = 10;
    public const DEFAULT_ORDER = 'id';
    public const DEFAULT_PAGE = 1;

    /**
     * @Serializer\Type("string")
     *
     * @var string|null
     */
    public $id;

    /**
     * @Serializer\Type("string")
     *
     * @var string
     */
    public $order = self::DEFAULT_ORDER;

    /**
     * @Serializer\Type("string")
     * @Assert\GreaterThanOrEqual(1)
     * @Assert\LessThanOrEqual(999)
     *
     * @var integer
     */
    public $page = self::DEFAULT_PAGE;

    /**
     * @Serializer\Type("string")
     * @Assert\GreaterThanOrEqual(0)
     * @Assert\LessThanOrEqual(999)
     *
     * @var integer
     */
    public $limit = self::DEFAULT_LIMIT;

    /**
     * @Serializer\Type("array")
     *
     * @var array<int, array<string, string>>
     */
    public $modifiers = [];

    /**
     * @Serializer\Type("string")
     * @Assert\Choice(choices = {"list", "details"})
     *
     * @var string
     */
    public $datagroup = self::DEFAULT_DATAGROUP;

    /**
     * Gets the value of a property
     *
     * @param string $key
     * @param mixed|null $defaultValue
     * @param string|null $type
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get(
        string $key,
        $defaultValue = null,
        string $type = null
    ) {
        if (!property_exists($this, $key)) {
            throw new \InvalidArgumentException(sprintf("The property '%s' does not exist in %s", $key, get_class($this)));
        }

        if (method_exists($this, 'get' . ucwords($key))) {
            // The following is the equivalent of call_user_func([$this, 'get' . ucwords($key)]);
            // However, phpstan does not support this as it is not verifiable as callable and it reports, in levels
            // above 6, this ambiguity as a failure.
            return ($this)::{'get' . ucwords($key)}() ?? $defaultValue;
        }

        return $this->parseParam($key, $defaultValue, $type);
    }

    /**
     * @param string $modifier
     *
     * @return array<string, mixed>
     */
    public function getModifier(string $modifier): array
    {
        /** @var array<string, array<string, mixed>> */
        $modifiers = $this->get('modifiers', [], 'array');

        return count($modifiers[$modifier]) >= 1 ? $modifiers[$modifier] : [];
    }

    /**
     * Gets the value of a link
     *
     * @param string $key
     * @param mixed|null $defaultValue
     *
     * @return \Sixpaths\Platform\Symfony\Entity\AbstractEntity|mixed|null
     * @throws \InvalidArgumentException
     */
    public function getLink(
        string $key,
        $defaultValue = null
    ) {
        if (!property_exists($this, '_links')) {
            throw new \InvalidArgumentException(sprintf("The property '%s' does not exist in %s", $key, get_class($this)));
        }

        if (method_exists($this, 'get' . ucwords($key))) {
            // The following is the equivalent of call_user_func([$this, 'get' . ucwords($key)]);
            // However, phpstan does not support this as it is not verifiable as callable and it reports, in levels
            // above 6, this ambiguity as a failure.
            return ($this)::{'get' . ucwords($key)}() ?? $defaultValue;
        }

        return $this->parseParam($key, $defaultValue);
    }

    /**
     * @param string $key
     * @param mixed|null $defaultValue
     * @param string|null $type
     *
     * @return mixed
     */
    private function parseParam(
        string $key,
        $defaultValue = null,
        string $type = null
    ) {
        $result = $this->$key;

        switch ($type) {
            case 'array':
            case 'order':
                $result = $defaultValue;

                if (is_string($this->$key)) {
                    $result = [];
                    $array = explode(',', $this->$key);
                    foreach ($array as $value) {
                        if (substr($value, 0, 1) === '-') {
                            $result[substr($value, 1)] = false;

                            continue;
                        }

                        $result[$value] = true;
                    }
                } elseif (is_array($this->$key)) {
                    $result = $this->$key;
                }
                break;

            case 'integer':
                if (!is_numeric($this->$key)) {
                    $result = $defaultValue;
                }

                $result = (int) $this->$key;

                break;

            case 'boolean':
                // Special case for null values in order that a false is not returned
                if ($result === null) {
                    return $defaultValue;
                }

                $result = filter_var($result, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                break;
        }

        return $result ?? $defaultValue;
    }
}
