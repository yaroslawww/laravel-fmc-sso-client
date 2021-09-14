<?php

namespace FMCSSOClient;

use FMCSSOClient\Exceptions\CodeErrorException;
use FMCSSOClient\Exceptions\InvalidResponseUrlException;
use FMCSSOClient\Exceptions\InvalidStateException;
use FMCSSOClient\Exceptions\InvalidTokenResponseException;
use FMCSSOClient\Exceptions\InvalidUserResponseException;
use FMCSSOClient\OAuth\RoutesManager;
use FMCSSOClient\OAuth\StateManager;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class SSOClient
{

    /**
     * Object to manipulate oAuth state.
     *
     * @var StateManager
     */
    protected StateManager $stateManager;

    /**
     * Object to manipulate oAuth routes.
     *
     * @var RoutesManager
     */
    protected RoutesManager $routesManager;

    /**
     * The HTTP Client instance.
     *
     * @var PendingRequest|null
     */
    protected ?PendingRequest $httpClient = null;

    /**
     * The client ID.
     *
     * @var string
     */
    protected string $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected string $clientSecret;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected string $redirectUrl;

    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * The custom Guzzle configuration options.
     *
     * @var array
     */
    protected array $guzzle = [];

    /**
     * FMC SSO user.
     *
     * @var SSOUser|null
     */
    protected ?SSOUser $ssoUser = null;

    /**
     * SSOClient constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param array $ssoConfig
     * @param array $guzzle
     */
    public function __construct(string $clientId, string $clientSecret, string $redirectUrl, array $ssoConfig = [], array $guzzle = [])
    {
        $this->stateManager  = new StateManager((bool) $ssoConfig['useState']);
        $this->routesManager = new RoutesManager(
            $ssoConfig['domain'],
            $ssoConfig['authorizePath'],
            $ssoConfig['tokenPath'],
            $ssoConfig['userPath'],
            $ssoConfig['scopeSeparator'],
            (bool) $ssoConfig['ssl']
        );

        $this->clientId     = $clientId;
        $this->redirectUrl  = $redirectUrl;
        $this->clientSecret = $clientSecret;

        $this->guzzle = $guzzle;
    }

    /**
     * Redirect to SSO authorize url.
     *
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse
    {
        return new RedirectResponse($this->routesManager->getAuthUrl($this->getCodeFields()));
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param string|null $state
     *
     * @return array
     */
    protected function getCodeFields(?string $state = null): array
    {
        $fields = [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUrl,
            'scope'         => $this->routesManager->prepareScopes($this->getScopes()),
            'response_type' => 'code',
            'state'         => $this->stateManager->makeState(),
        ];

        return array_filter(array_merge($fields, $this->parameters));
    }

    /**
     * Set the scopes of the requested access.
     *
     * @param array|string $scopes
     *
     * @return static
     */
    public function setScopes(array|string $scopes): SSOClient
    {
        $this->scopes = array_unique((array) $scopes);

        return $this;
    }

    /**
     * Get the current scopes.
     *
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Set the custom parameters of the request.
     *
     * @param array $parameters
     *
     * @return static
     */
    public function with(array $parameters): SSOClient
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * OAuth part 2, get state and find info about user.
     *
     * @return SSOUser
     *
     * @throws InvalidUserResponseException
     */
    public function ssoUser(): SSOUser
    {
        if ($this->ssoUser) {
            return $this->ssoUser;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException('FMC SSO response contain not valid "state".');
        }

        if (!is_null($errorType = $this->getCodeError())) {
            throw new CodeErrorException($errorType);
        }

        $token = $this->getAccessTokenResponse($this->getCode());

        $this->ssoUser = $this->mapUserToObject($this->getUserByToken($token));

        return $this->ssoUser->setToken($token);
    }

    /**
     * Get the code from the request.
     *
     * @return string
     */
    protected function getCode(): string
    {
        $code = request()->input('code', '');
        if (empty($code) || !is_string($code)) {
            throw new InvalidResponseUrlException('Code parameter is empty or not valid');
        }

        return $code;
    }

    /**
     * Get the error from the request.
     *
     * @return string|null
     */
    protected function getCodeError(): ?string
    {
        if (request()->has('error')) {
            return (string) request()->input('error', '');
        }

        return null;
    }

    /**
     * Determine if the current request / session has a mismatching "state".
     *
     * @return bool
     */
    protected function hasInvalidState(): bool
    {
        if (!$this->stateManager->needValidateState()) {
            return false;
        }
        $state = $this->stateManager->pullState();

        return !(strlen($state) > 0 && request()->input('state') === $state);
    }

    /**
     * Get the access token response for the given code.
     *
     * @param string $code
     *
     * @return Token
     * @throws InvalidTokenResponseException
     */
    public function getAccessTokenResponse(string $code): Token
    {
        $response = $this->getHttpClient()
                         ->post($this->routesManager->getTokenUrl(), $this->getTokenFields($code));

        return $this->getTokenFromResponse($response);
    }

    /**
     * Refresh token.
     *
     * @param Token $token
     *
     * @return Token|null
     * @throws InvalidTokenResponseException
     */
    public function refreshToken(Token $token): ?Token
    {
        if (!$token->valid() || !$token->getRefreshToken()) {
            return null;
        }
        $response = $this->getHttpClient()
                         ->post($this->routesManager->getTokenUrl(), $this->getRefreshTokenFields($token->getRefreshToken()));

        return $this->getTokenFromResponse($response);
    }

    /**
     * Get an instance of the Guzzle HTTP client.
     *
     * @return PendingRequest
     */
    protected function getHttpClient(): PendingRequest
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = Http::withOptions($this->guzzle)
                                    ->withHeaders([ 'Accept' => 'application/json' ]);
        }

        return $this->httpClient;
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param string $code
     *
     * @return array
     */
    protected function getTokenFields(string $code): array
    {
        return [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $this->redirectUrl,
        ];
    }

    /**
     * Get the POST fields for the refresh token request.
     *
     * @param string $refreshToken
     *
     * @return array
     */
    protected function getRefreshTokenFields(string $refreshToken): array
    {
        return [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => $this->routesManager->prepareScopes($this->getScopes()),
            'refresh_token' => $refreshToken,
        ];
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param string $token
     *
     * @return array
     * @throws InvalidUserResponseException
     */
    protected function getUserByToken(Token $token): array
    {
        $response = $this->getHttpClient()
                         ->withHeaders([ 'Authorization' => $token->getAuthorizationHeader() ])
                         ->get($this->routesManager->getUserUrl());

        $user = $response->json('data');

        if (!is_array($user) || empty($user)) {
            throw new InvalidUserResponseException('User response format not valid.');
        }


        return $user;
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     *
     * @return SSOUser
     */
    protected function mapUserToObject(array $user): SSOUser
    {
        return ( new SSOUser() )->setRaw($user)->map([
            'id'         => $user['id'],
            'title'      => Arr::get($user, 'title'),
            'first_name' => Arr::get($user, 'first_name'),
            'last_name'  => Arr::get($user, 'last_name'),
            'email'      => Arr::get($user, 'email'),
        ]);
    }

    /**
     * @param Response $response
     *
     * @return Token|null
     * @throws InvalidTokenResponseException
     */
    protected function getTokenFromResponse(Response $response): ?Token
    {
        if (!$response->successful()) {
            throw new InvalidTokenResponseException($response->json('message', $response->json('hint', 'Token response not valid.')));
        }

        try {
            $token = new Token(
                $response->json('token_type'),
                $response->json('expires_in'),
                $response->json('access_token'),
                $response->json('refresh_token'),
            );
        } catch (\TypeError $e) {
            throw new InvalidTokenResponseException(message: 'Token response has not valid params', previous: $e);
        }

        if (!$token->valid()) {
            throw new InvalidTokenResponseException('Token not valid or expired');
        }

        return $token;
    }
}
