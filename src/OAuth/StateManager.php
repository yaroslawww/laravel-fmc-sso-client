<?php

namespace FMCSSOClient\OAuth;

use Illuminate\Support\Str;

class StateManager
{
    protected bool $useState = true;

    protected string $cacheKey = 'state';

    /**
     * @param bool $useState
     */
    public function __construct(bool $useState = true)
    {
        $this->useState = $useState;
    }

    /**
     * Is need validate state.
     *
     * @return bool
     */
    public function needValidateState(): bool
    {
        return $this->useState;
    }

    /**
     * Create and cache state.
     *
     * @return string|null
     */
    public function makeState(): ?string
    {
        if (!$this->useState) {
            return null;
        }

        request()->session()->put($this->cacheKey, $state = $this->generateState());

        return $state;
    }

    /**
     * Get state and remove from cache.
     *
     * @return string|null
     */
    public function pullState(): ?string
    {
        if (!$this->useState) {
            return null;
        }

        return (string) request()->session()->pull($this->cacheKey);
    }

    /**
     * Get current state value.
     *
     * @return string|null
     */
    public function state(): ?string
    {
        if (!$this->useState) {
            return null;
        }

        return (string) request()->session()->get($this->cacheKey);
    }

    /**
     * Get the string used for session state.
     *
     * @return string
     */
    protected function generateState(): string
    {
        return Str::random(40);
    }
}
