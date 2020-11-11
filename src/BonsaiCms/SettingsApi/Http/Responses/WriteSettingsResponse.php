<?php

namespace BonsaiCms\SettingsApi\Http\Responses;

use Illuminate\Http\Response;
use BonsaiCms\SettingsApi\Contracts\WriteSettingsResponseContract;

class WriteSettingsResponse implements WriteSettingsResponseContract
{
    public function toResponse($writeCallbackResult)
    {
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
