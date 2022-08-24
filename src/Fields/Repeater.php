<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Fields;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;

/**
 * @method $this min($value = null)
 * @method $this max($value = null)
 * @method $this required($value = true)
 * @method $this help(string $value = null)
 * @method $this name($value = true)
 * @method $this title(string $value = null)
 */
class Repeater extends Field
{
    protected $view = 'platform::fields.repeater';

    protected $required = [
        'name',
        'layout',
    ];

    protected $attributes = [
        'class'                  => 'form-control',
        'original_name'          => null,
        'template'               => null,
        'button_label'           => null,
        'ajax_data'              => null,
        'confirmDeleteBlockText' => null,
    ];

    protected $inlineAttributes = [
        'required',
        'min',
        'max',
        'name',
        'ajax_data',
    ];

    public function layout(string $layout): self
    {
        if (! class_exists($layout) && ! (app($layout) instanceof Rows)) {
            throw new \InvalidArgumentException(
                __('":class" does not exists or not supported. Only rows supported by repeater.', [
                    'class' => $layout,
                ]));
        }

        $this->set('layout', Crypt::encryptString($layout));

        $this->addBeforeRender(function () {
            $value = $this->get('value') ?? old($this->getOldName());

            if (! is_iterable($value)) {
                $value = Arr::wrap($value);
            }

            $this->set('value', $value);
        });

        return $this;
    }

    public static function make(string $name = null): self
    {
        return (new static)->name($name)
            ->set('original_name', $name)
            ->value([])
            ->set('template', 'repeater_'.Str::random(32));
    }

    public function view(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    public function ajaxData(callable|array $value): self
    {
        if (is_callable($value)) {
            $value = call_user_func($value);
        }

        if (is_array($value)) {
            $this->set('ajax_data', json_encode($value, JSON_THROW_ON_ERROR));
        }

        return $this;
    }

    public function confirmText(string $confirmText): self
    {
        return $this->set('confirmDeleteBlockText', $confirmText);
    }

    public function buttonLabel(string $label): self
    {
        return $this->set('buttonLabel', $label);
    }

    public function collapse(bool $collapse): self
    {
        return $this->set('collapse', $collapse);
    }
}
