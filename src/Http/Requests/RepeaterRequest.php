<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepeaterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasAccess('platform.index');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'layout' => 'required',
            'repeater_name' => 'required',
            'values' => '',
        ];
    }
}
