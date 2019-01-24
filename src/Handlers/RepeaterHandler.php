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
     * Return fields
     *
     * @return array
     * @throws \Throwable
     */
    public final function handler()
    {
        $values = request('value', []);

        if (!$values) {
            return [$this->renderFields()];
        }

        $result = [];

        foreach ($values as $key => $value) {
            $result[] = $this->renderFields($key, $value);
        }

        return $result;
    }

    /**
     * @param int $list
     * @param array $values
     * @return array
     * @throws \Throwable
     */
    private function renderFields(int $list = 0, $values = [])
    {
        $fields = [];
        foreach ($this->fields() as $field) {
            if ($field instanceof Field) {
                $oldName = $field->get('name');
                if (array_has($values, $oldName)) {
                    $field->modifyValue($values[$oldName]);
                }

                $fieldName = $this->repeaterName . '[' . $list . '][' . $oldName . ']';

                $field->modifyName($fieldName);
                $fields[] = $field->render()->render();
            }
        }

        return $fields;
    }
}