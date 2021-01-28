<?php

namespace App\Http\Requests;

use App\Models\DebitCard;
use App\Models\DebitCardTransaction;
use Illuminate\Foundation\Http\FormRequest;

class DebitCardTransactionShowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->route('debitCardTransaction'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
