<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Handlers;

use Orchid\Screen\Field;
use Illuminate\View\View;
use Orchid\Widget\Widget;

/**
 * Modifying the fields to the correct data. Allow to send data to this widget.
 *
 * Class RepeaterHandler
 */
abstract class RepeaterHandler extends Widget
{
    /**
     * Repeater name which will be used as prefix.
     *
     * @var array|\Illuminate\Http\Request|string
     */
    public $repeaterName;

    /**
     * How much blocks we need generate in one request.
     *
     * @var array|\Illuminate\Http\Request|int|string
     */
    public $num = 1;

    /**
     * @var null
     */
    public $key = null;

    public function __construct()
    {
        $this->repeaterName = request('repeater_name', null);
        $num = request('num', 1);

        if ($num >= 1) {
            $this->num = $num;
        }
    }

    /**
     * Return array of the fields.
     *
     * @return Field[]
     */
    abstract public function fields(): array;

    /**
     * Return rendered fields.
     *
     * @return array
     * @throws \Throwable
     */
    final public function handler()
    {
        $values = null;

        if (request()->has('value')) {
            $values = request('value', null);
        }

        $blocksCount = (int) request('blocks', 0);
        $result = [];

        if (is_null($values)) {
            for ($i = 0; $i < $this->num; $i++) {
                $result[] = $this->renderFields($blocksCount + $i);
            }
        } else {
            foreach ($values as $key => $value) {
                $result[] = $this->renderFields($key, $value);
            }
        }

        return $result;
    }

    /**
     * Preparing fields and modifying their by names.
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
                $name = $field->get('name');
                $bindValueName = $name;
                $isArray = false;
                if (substr($name, -1) === '.') {
                    $bindValueName = substr($bindValueName, 0, -1);
                    $isArray = true;
                }

                if (array_has($values, $bindValueName)) {
                    $field->modifyValue($values[$bindValueName]);
                }

                $fieldName = $this->repeaterName.'['.$blockKey.']['.$bindValueName.']';

                if (true === $isArray) {
                    $fieldName .= '[]';
                }

                $field->modifyName($fieldName);

                //Using this for reorder
                $field->attributes['data-repeater-name-key'] = $name;
                $field->inlineAttributes[] = 'data-repeater-name-key';

                $view = $field->render();

                if ($view instanceof View) {
                    $fields[] = $view->render();
                }
            }
        }

        return $fields;
    }
}
