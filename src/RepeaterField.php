<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField;

use Orchid\Screen\Field;

/**
 * Creating repeater fields based on the fields which provided to the endpoint.
 *
 * Class RepeaterField
 *
 * @method $this min($value = null)
 * @method $this max($value = null)
 * @method $this required($value = true)
 * @method $this help(string $value = null)
 * @method $this name($value = true)
 * @method $this handler($value = true)
 */
class RepeaterField extends Field
{
    /**
     * View name.
     *
     * @var string
     */
    public $view = 'platform::fields.repeater';

    /**
     * Required Attributes.
     *
     * @var array
     */
    public $required = [
        'name',
        'handler',
    ];

    /**
     * Default attributes value.
     *
     * @var array
     */
    public $attributes = [
        'class' => 'form-control',
        'original_name' => null,
        'template' => null,
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
        'name',
    ];

    /**
     * Set the template unique id
     *
     * @return self
     */
    public function setTemplateId(): self
    {
        $key = 'repeater_'.str_random(8).'_'.str_random(12);

        return $this->set('template', $key);
    }

    /**
     * Creating an instance of the repeater field.
     *
     * @param string $name
     * @return RepeaterField
     */
    public static function make(string $name): self
    {
        return (new static)->name($name)
            ->set('original_name', $name)
            ->value([])
            ->setTemplateId();
    }
}
