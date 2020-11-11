<?php

namespace BonsaiCms\SettingsApi\Contracts;

interface ReadSettingsResponseContract
{
    function toResponse($values);
}
