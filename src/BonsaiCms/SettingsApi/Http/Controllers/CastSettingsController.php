<?php

namespace BonsaiCms\SettingsApi\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use BonsaiCms\Settings\Contracts\SettingsManager;
use BonsaiCms\SettingsApi\Contracts\ReadSettingsRequestContract;
use BonsaiCms\SettingsApi\Contracts\WriteSettingsRequestContract;

abstract class CastSettingsController extends SettingsController
{
    const CAST_DELIMITER = '|';

    /*
     * Temporary store original keys here
     */
    protected $originalKeys;

    /*
     * key of this array = setting key
     * value of this array = array of casts for the setting key
     */
    protected $castKeysTo = [];

    /*
     * Array of registered after-read casters
     */
    protected $afterReadCasters = [];

    /*
     * Array of registered before-write casters
     */
    protected $beforeWriteCasters = [];

    public function __construct(SettingsManager $settings)
    {
        parent::__construct($settings);

        $this->registerCasters();
    }

    /*
     * Override parent transform methods
     */

    protected function transformKeysAfterExtractedFromRequest($keys, ReadSettingsRequestContract $request)
    {
        $this->originalKeys = (new Collection($keys))->unique();

        return (new Collection($keys))->map(function ($key) {
            foreach (array_keys($this->afterReadCasters) as $cast) {
                if ($this->keyHasCast($key, $cast)) {
                    $key = $this->getPureKey($key);
                    $this->castKey($key, $cast);
                    return $key;
                }
            }
            if ($this->keyHasAnyCast($key)) {
                $this->throwUnknownCastException($key);
            }
            return $key;
        })->unique()->toArray();
    }

    protected function transformDataAfterExtractedFromRequest($data, WriteSettingsRequestContract $request)
    {
        $data = parent::transformDataAfterExtractedFromRequest($data, $request);

        $data = (new Collection($data));

        // Check
        $uniquePureKeys = $data->keys()->map(function ($key) {
            return $this->getPureKey($key);
        })->unique();
        if ($uniquePureKeys->count() !== $data->count()) {
            $this->throwMultipleCastsOnTheSameKeyException(
                $this->getPureKey(
                    $data->keys()->diff($uniquePureKeys)->first()
                )
            );
        }

        return $data->mapWithKeys(function ($value, $originalKey) {
            list($pureKey, $cast) = $this->explodeKey($originalKey);
            return [$pureKey => $this->castValueBeforeWrite($cast, $value, $originalKey, $pureKey)];
        });
    }

    protected function transformValuesAfterRead($values, $keys, ReadSettingsRequestContract $request)
    {
        $values = parent::transformValuesAfterRead($values, $keys, $request);

        if (count($this->castKeysTo) === 0) {
            return $values;
        }

        return $this->originalKeys->mapWithKeys(function ($originalKey) use ($values) {
            list($pureKey, $cast) = $this->explodeKey($originalKey);
            return [$originalKey => $this->castValueAfterRead($cast, $values[$pureKey], $originalKey, $pureKey)];
        });
    }

    /*
     * Casters methods
     */

    abstract protected function registerCasters();

    protected function registerAfterReadCaster($format, $caster)
    {
        $this->afterReadCasters[$format] = $caster;
    }

    protected function registerBeforeWriteCaster($format, $caster)
    {
        $this->beforeWriteCasters[$format] = $caster;
    }

    protected function castValueAfterRead($cast, $value, $originalKey, $pureKey)
    {
        return ($cast === null)
            ? $value
            : $this->afterReadCasters[$cast]($cast, $value, $originalKey, $pureKey);
    }

    protected function castValueBeforeWrite($cast, $value, $originalKey, $pureKey)
    {
        return ($cast === null)
            ? $value
            : $this->beforeWriteCasters[$cast]($cast, $value, $originalKey, $pureKey);
    }

    /*
     * Smart append to $castKeysTo property
     */

    protected function castKey($key, $cast)
    {
        if (!isset($this->castKeysTo[$key])) {
            $this->castKeysTo[$key] = new Collection;
        }
        $this->castKeysTo[$key] = $this->castKeysTo[$key]->push($cast)->unique();
    }

    /*
     * Helpers
     */

    protected function explodeKey($key)
    {
        return [
            $this->getPureKey($key),
            $this->keyHasAnyCast($key)
                ? $this->getCastFromKey($key)
                : null,
        ];
    }

    protected function keyHasCast($key, $cast = null)
    {
        return Str::endsWith($key, static::CAST_DELIMITER . $cast);
    }

    protected function keyHasAnyCast($key)
    {
        return Str::contains($key, static::CAST_DELIMITER);
    }

    protected function getPureKey($key)
    {
        return Str::beforeLast($key, static::CAST_DELIMITER);
    }

    protected function getCastFromKey($key)
    {
        return Str::afterLast($key, static::CAST_DELIMITER);
    }

    /*
     * Exception throwers
     */

    protected function throwUnknownCastException($originalKey)
    {
        list($pureKey, $cast) = $this->explodeKey($originalKey);
        throw ValidationException::withMessages([
            static::REQUEST_KEYS_FIELD => "Key {$pureKey} contains unknown cast format {$cast}"
        ]);
    }

    protected function throwMultipleCastsOnTheSameKeyException($key)
    {
        throw ValidationException::withMessages([
            static::REQUEST_DATA_FIELD => "Key {$key} is casted in multiple formats."
        ]);
    }
}
