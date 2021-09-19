<?php

namespace FMCSSOClient;

use Carbon\Carbon;

class Token
{
    protected string $tokenType;

    protected ?\DateTimeInterface $expiresAt;

    protected string $accessToken;

    protected string $refreshToken;

    /**
     * @param string $tokenType
     * @param int $expiresIn
     * @param string $accessToken
     * @param string $refreshToken
     */
    public function __construct(string $tokenType, int $expiresIn, string $accessToken, string $refreshToken)
    {
        $this->tokenType    = $tokenType;
        $this->expiresAt    = Carbon::now()->addSeconds($expiresIn);
        $this->accessToken  = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    /**
     * Check is access token valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->accessToken && Carbon::now()->addMinute()->lessThan($this->expiresAt);
    }

    /**
     * @return \DateTimeInterface
     */
    public function getExpiresAt(): \DateTimeInterface
    {
        if ($this->valid()) {
            return $this->expiresAt;
        }

        return Carbon::now();
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * Get Authorization header value
     *
     * @return string|null
     */
    public function getAuthorizationHeader(): ?string
    {
        if ($this->valid()) {
            return "{$this->tokenType} {$this->getAccessToken()}";
        }

        return null;
    }

    public function __serialize(): array
    {
        return [
            'tokenType'    => $this->tokenType,
            'refreshToken' => $this->refreshToken,
            'accessToken'  => $this->accessToken,
            'expiresAt'    => $this->expiresAt->getTimestamp(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->tokenType    = $data['tokenType']    ?? '';
        $this->accessToken  = $data['accessToken']  ?? '';
        $this->refreshToken = $data['refreshToken'] ?? '';
        $this->expiresAt    = Carbon::createFromTimestamp($data['expiresAt'] ?? 0);
    }
}
