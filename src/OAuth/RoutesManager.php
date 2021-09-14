<?php

namespace FMCSSOClient\OAuth;

class RoutesManager
{

    /**
     * Use https.
     *
     * @var bool
     */
    protected bool $ssl = true;

    /**
     * The auth domain.
     *
     * @var string
     */
    protected string $domain = '';

    /**
     * The authorize path.
     *
     * @var string
     */
    protected string $authorizePath = '/';

    /**
     * The token path.
     *
     * @var string
     */
    protected string $tokenPath = '/';

    /**
     * The user path.
     *
     * @var string
     */
    protected string $userPath = '/';

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected string $scopeSeparator = ' ';

    /**
     * The type of the encoding in the query.
     *
     * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738.
     */
    protected int $encodingType = PHP_QUERY_RFC1738;


    public function __construct(
        string $domain,
        string $authorizePath = '/oauth/authorize',
        string $tokenPath = '/oauth/token',
        string $userPath = '/json/profile',
        string $scopeSeparator = ' ',
        bool $ssl = true
    ) {
        $this->domain         = $domain;
        $this->authorizePath  = $authorizePath;
        $this->tokenPath      = $tokenPath;
        $this->userPath       = $userPath;
        $this->scopeSeparator = $scopeSeparator;
        $this->ssl            = $ssl;
    }

    /**
     * Get "authorize" url
     *
     * @param array $codeFields
     *
     * @return string
     */
    public function getAuthUrl(array $codeFields): string
    {
        return $this->buildAuthUrlFromBase($this->buildUrlFromPath($this->authorizePath), $codeFields);
    }

    /**
     * Get "token" url
     *
     * @return string
     */
    public function getTokenUrl(): string
    {
        return $this->buildUrlFromPath($this->tokenPath);
    }

    /**
     * Get "user information" url
     *
     * @return string
     */
    public function getUserUrl(): string
    {
        return $this->buildUrlFromPath($this->userPath);
    }

    /**
     * Build url from path value.
     *
     * @param string $path
     *
     * @return string
     */
    public function buildUrlFromPath(string $path): string
    {
        return ($this->ssl ? 'https' : 'http') . '://' . rtrim($this->domain, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Build the authentication URL for the provider from the given base URL.
     *
     * @param string $url
     * @param array $codeFields
     *
     * @return string
     */
    protected function buildAuthUrlFromBase(string $url, array $codeFields): string
    {
        return $url . '?' . http_build_query($codeFields, '', '&', $this->encodingType);
    }

    /**
     * Prepare scopes for request.
     *
     * @param array $scopes
     *
     * @return string
     */
    public function prepareScopes(array $scopes): string
    {
        return $this->formatScopes($scopes, $this->scopeSeparator);
    }

    /**
     * Format the given scopes.
     *
     * @param array $scopes
     * @param string $scopeSeparator
     *
     * @return string
     */
    protected function formatScopes(array $scopes, string $scopeSeparator = ' '): string
    {
        return implode($scopeSeparator, $scopes);
    }
}
