<?php

namespace Eleadtech\AlphabetOption\Model;

class SortType
{
    /**#@+
     * Status values
     */
    const POSITION = 0;
    const ASC = 1;
    const DESC = 2;

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [self::POSITION => __('Position'), self::ASC => __('ASC'), self::DESC => __('DESC') ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public static function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
