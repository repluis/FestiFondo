<?php

namespace Src\Transactions\Infrastructure\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'             => 'required|string|in:income,expense',
            'member_oid'       => 'nullable|integer|exists:members,oid',
            'amount'           => 'required|numeric|min:0.01',
            'description'      => 'required|string|max:255',
            'reference'        => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string|max:1000',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
