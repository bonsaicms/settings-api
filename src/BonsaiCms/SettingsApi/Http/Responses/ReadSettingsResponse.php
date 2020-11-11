<?php

namespace BonsaiCms\SettingsApi\Http\Responses;

use Illuminate\Http\JsonResponse;
use BonsaiCms\SettingsApi\Contracts\ReadSettingsResponseContract;

class ReadSettingsResponse implements ReadSettingsResponseContract
{
    public function toResponse($values)
    {
        return new JsonResponse(count($values) ? $values : null);
    }
}
