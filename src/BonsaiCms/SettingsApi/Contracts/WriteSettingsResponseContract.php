<?php

namespace BonsaiCms\SettingsApi\Contracts;

interface WriteSettingsResponseContract
{
    function toResponse($writeCallbackResult);
}
