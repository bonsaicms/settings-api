<?php

namespace BonsaiCms\SettingsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use BonsaiCms\SettingsApi\Http\Controllers\SettingsController;
use BonsaiCms\SettingsApi\Contracts\ReadSettingsRequestContract;

class ReadSettingsRequest extends FormRequest implements ReadSettingsRequestContract
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            SettingsController::REQUEST_KEYS_FIELD => [
                'nullable',
                'array',
            ],
        ];
    }
}
