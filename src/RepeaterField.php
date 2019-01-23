<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField;

use Orchid\Screen\Field;

/**
 * Creating repeater fields based on the fields which provided to the endpoint
 *
 * Class RepeaterField
 * @package Nakukryskin\OrchidRepeaterField
 *
 * @method $this min($value = null)
 * @method $this max($value = null)
 * @method $this required($value = true)
 * @method $this help(string $value = null)
 * @method $this name($value = true)
 */
class RepeaterField extends Field
{
    /**
     * View name
     *
     * @var string
     */
    public $view = 'platform::fields.repeater';

    /**
     * Default attributes value.
     *
     * @var array
     */
    public $attributes = [
        'class' => 'form-control',
        'fields' => []
    ];

    /**
     * Attributes available for a particular tag.
     *
     * @var array
     */
    public $inlineAttributes = [
        'required',
        'min',
        'max',
        'name'
    ];


    public $fields;

    /**
     * Set the fields array
     *
     * @param array $fields
     * @return RepeaterField
     * @throws \Throwable
     */
    public function fields(array $fields): self
    {
        foreach ($fields as $field) {
            if ($field instanceof Field) {
                $this->attributes['fields'][] = $field->render();
            }
        }

        return $this;
    }

    /**
     * Creating an instance of the repeater field
     *
     * @param string $name
     * @param array $fields
     * @return RepeaterField
     * @throws \Throwable
     */
    public static function make(string $name, array $fields): self
    {
        return (new static)->name($name)->fields($fields);
    }
}