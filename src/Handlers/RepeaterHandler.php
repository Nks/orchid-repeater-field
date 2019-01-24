<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Handlers;

use Orchid\Screen\Field;
use Orchid\Widget\Widget;

/**
 * Modifying the fields to the correct data. Allow to send data to this widget
 *
 * Class RepeaterHandler
 * @package App\Orchid\Fields
 */
abstract class RepeaterHandler extends Widget
{
    public $repeaterName;

    /**
     * @var null
     */
    public $key = null;

    public function __construct()
    {
        $this->repeaterName = request('repeater_name', null);
    }

    /**
     * Return array of the fields
     *
     * @return Field[]
     */
    abstract function fields(): array;

    /**
     * Return rendered fields
     *
     * @return array
     * @throws \Throwable
     */
    public final function handler()
    {
        $values = null;

        if (request()->has('value')) {
            $values = request('value', null);
        }

        $blocksCount = (int)request('blocks', 0);

        if (is_null($values)) {
            return [$this->renderFields($blocksCount)];
        }

        $result = [];

        foreach ($values as $key => $value) {
            $result[] = $this->renderFields($key, $value);
        }

        return $result;
    }

    /**
     * Preparing fields and modifying their by names
     *
     * TODO Add support of multiple values (select[])
     *
     * @param int $blockKey
     * @param array $values
     * @return array
     * @throws \Throwable
     */
    private function renderFields(int $blockKey = 0, $values = [])
    {
        $fields = [];
        foreach ($this->fields() as $field) {
            if ($field instanceof Field) {
                $oldName = $field->get('name');
                if (array_has($values, $oldName)) {
                    $field->modifyValue($values[$oldName]);
                }

                $fieldName = $this->repeaterName . '[' . $blockKey . '][' . $oldName . ']';

                $field->modifyName($fieldName);
                //Using this for reorder
                $field->attributes['data-repeater-name-key'] = $oldName;
                $field->inlineAttributes[] = 'data-repeater-name-key';
                $fields[] = $field->render()->render();
            }
        }

        return $fields;
    }
}