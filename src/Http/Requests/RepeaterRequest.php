<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepeaterRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasAccess('platform.index');
    }

    public function rules(): array
    {
        return [
            'blocks'        => ['nullable', 'integer'],
            'num'           => ['nullable', 'integer'],
            'layout'        => ['required', 'string'],
            'repeater_name' => ['required', 'string'],
            'values'        => ['nullable', 'array'],
            'repeater_data' => ['nullable', 'array'],
        ];
    }
}
