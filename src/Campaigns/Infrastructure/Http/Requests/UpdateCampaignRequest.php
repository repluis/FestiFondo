<?php

namespace Src\Campaigns\Infrastructure\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => 'required|string|max:200',
            'description'        => 'nullable|string|max:2000',
            'target_amount'      => 'required|numeric|min:0.01',
            'monthly_fee_amount' => 'required|numeric|min:0.01',
            'daily_penalty_rate' => 'required|numeric|min:0',
            'due_day'            => 'required|integer|min:1|max:28',
            'start_date'         => 'required|date_format:Y-m-d',
            'end_date'           => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'campaign_status'    => 'required|string|in:draft,active,completed,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'The campaign name is required.',
            'target_amount.required'   => 'The target amount is required.',
            'target_amount.min'        => 'The target amount must be greater than zero.',
            'start_date.required'      => 'The start date is required.',
            'end_date.after_or_equal'  => 'The end date must be on or after the start date.',
            'campaign_status.required' => 'The campaign status is required.',
            'campaign_status.in'       => 'Invalid status. Allowed: draft, active, completed, cancelled.',
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => false,
                'message' => 'The submitted data is not valid.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
