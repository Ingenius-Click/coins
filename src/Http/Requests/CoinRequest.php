<?php

namespace Ingenius\Coins\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Ingenius\Coins\Enums\CoinPosition;

class CoinRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:10'],
            'symbol' => ['required', 'string', 'max:5'],
            'position' => ['required', 'string', 'in:' . implode(',', array_column(CoinPosition::cases(), 'value'))],
            'active' => ['boolean'],
            'main' => ['boolean'],
            'exchange_rate' => ['numeric', 'min:0'],
            'exchange_rate_history' => ['nullable', 'array'],
        ];

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
