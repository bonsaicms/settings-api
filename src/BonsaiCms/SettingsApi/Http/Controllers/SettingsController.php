<?php

namespace BonsaiCms\SettingsApi\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use BonsaiCms\Settings\Contracts\SettingsManager;

use BonsaiCms\SettingsApi\Contracts\ReadSettingsRequestContract;
use BonsaiCms\SettingsApi\Contracts\WriteSettingsRequestContract;

use BonsaiCms\SettingsApi\Contracts\ReadSettingsResponseContract;
use BonsaiCms\SettingsApi\Contracts\WriteSettingsResponseContract;

class SettingsController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    const REQUEST_KEYS_FIELD = 'keys';
    const REQUEST_DATA_FIELD = 'data';

    protected $settings;

    public function __construct(SettingsManager $settings)
    {
        $this->settings = $settings;
    }

    // Read process

    public function read(ReadSettingsRequestContract $request)
    {
        $keys = $this->extractKeysFromRequest($request);
        $keys = $this->transformKeysAfterExtractedFromRequest($keys, $request);
        $readCallback = $this->resolveReadCallback($keys, $request);
        $values = $this->performRead($readCallback);
        $values = $this->transformValuesAfterRead($values, $keys, $request);
        return $this->sendReadResponse($values);
    }

    // Write process

    public function write(WriteSettingsRequestContract $request)
    {
        $data = $this->extractDataFromRequest($request);
        $data = $this->transformDataAfterExtractedFromRequest($data, $request);
        $writeCallback = $this->resolveWriteCallback($data, $request);
        $writeCallbackResult = $this->performWrite($writeCallback);
        return $this->sendWriteResponse($writeCallbackResult);
    }

    // Read methods

    protected function extractKeysFromRequest(ReadSettingsRequestContract $request)
    {
        return $request->get(static::REQUEST_KEYS_FIELD);
    }

    protected function transformKeysAfterExtractedFromRequest($keys, ReadSettingsRequestContract $request)
    {
        return $keys;
    }

    protected function resolveReadCallback($keys, ReadSettingsRequestContract $request)
    {
        if ($keys && is_array($keys)) {
            return function () use ($keys) {
                return $this->settings->get($keys);
            };
        } else {
            return function ()  {
                return $this->settings->all();
            };
        }
    }

    protected function performRead(callable $readCallback)
    {
        return $readCallback();
    }

    protected function transformValuesAfterRead($values, $keys, ReadSettingsRequestContract $request)
    {
        return $values;
    }

    protected function sendReadResponse($values)
    {
        return resolve(ReadSettingsResponseContract::class)->toResponse($values);
    }

    // Write methods

    protected function extractDataFromRequest(WriteSettingsRequestContract $request)
    {
        return $request->get(static::REQUEST_DATA_FIELD);
    }

    protected function transformDataAfterExtractedFromRequest($data, WriteSettingsRequestContract $request)
    {
        return collect($data);
    }

    protected function resolveWriteCallback($data, WriteSettingsRequestContract $request)
    {
        return function () use ($data) {
            $this->settings->set($data);
        };
    }

    protected function performWrite(callable $writeCallback)
    {
        return $writeCallback();
    }

    protected function sendWriteResponse($writeCallbackResult)
    {
        return resolve(WriteSettingsResponseContract::class)->toResponse($writeCallbackResult);
    }
}
