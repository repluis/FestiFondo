<?php

namespace Src\Members\Infrastructure\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'nullable|email|max:150',
            'phone'      => 'nullable|string|max:30',
            'address'    => 'nullable|string|max:500',
            'notes'      => 'nullable|string|max:1000',
            'joined_at'  => 'required|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'  => 'The first name is required.',
            'last_name.required'   => 'The last name is required.',
            'email.email'          => 'The email address is not valid.',
            'joined_at.required'   => 'The join date is required.',
            'joined_at.date_format' => 'The join date must be in YYYY-MM-DD format.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'First Name',
            'last_name'  => 'Last Name',
            'email'      => 'Email Address',
            'phone'      => 'Phone Number',
            'joined_at'  => 'Join Date',
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        if ($this->header('X-Inertia') === 'true') {
            throw new HttpResponseException(
                redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->setStatusCode(303)
            );
        }

        throw new HttpResponseException(
            response()->json([
                'status'  => false,
                'message' => 'The submitted data is not valid.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
