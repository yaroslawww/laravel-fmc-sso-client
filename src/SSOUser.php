<?php

namespace FMCSSOClient;

use Illuminate\Support\Arr;

class SSOUser
{
    /**
     * The user's access token.
     *
     * @var Token
     */
    protected Token $token;

    /**
     * The unique identifier for the user.
     *
     * @var int|string
     */
    protected int|string $id;

    /**
     * The user's e-mail address.
     *
     * @var string
     */
    protected string $email;

    /**
     * The user's title.
     *
     * @var string
     */
    protected string $title;

    /**
     * The user's first name.
     *
     * @var string
     */
    protected string $first_name;

    /**
     * The user's last name.
     *
     * @var string
     */
    protected string $last_name;

    /**
     * The user's raw attributes.
     *
     * @var array
     */
    protected array $user;

    /**
     * Set the token on the user.
     *
     * @param Token $token
     *
     * @return static
     */
    public function setToken(Token $token): SSOUser
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Access token instance.
     *
     * @return Token
     */
    public function token(): Token
    {
        return $this->token;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the name title of the user.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the first name of the user.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /**
     * Get the last name of the user.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->last_name;
    }

    /**
     * Get the name of the user.
     *
     * @return string
     */
    public function getName(): string
    {
        return implode(' ', array_filter([
            $this->title,
            $this->first_name,
            $this->last_name,
        ]));
    }

    /**
     * Get the e-mail address of the user.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Get the raw user array.
     *
     * @return array
     */
    public function getRaw(): array
    {
        return $this->user;
    }

    /**
     * Set the raw user array from the provider.
     *
     * @param array $user
     *
     * @return $this
     */
    public function setRaw(array $user): SSOUser
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Map the given array onto the user's properties.
     *
     * @param array $attributes
     *
     * @return $this
     */
    public function map(array $attributes): SSOUser
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Determine if the given raw user attribute exists.
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists(string $offset): bool
    {
        return Arr::exists($this->user, $offset);
    }

    /**
     * Get the given key from the raw user.
     *
     * @param string $offset
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function offsetGet(string $offset, mixed $default = null): mixed
    {
        return Arr::get($this->user, $offset, $default);
    }

    /**
     * Set the given attribute on the raw user array.
     *
     * @param string $offset
     * @param mixed $value
     *
     * @return static
     */
    public function offsetSet(string $offset, mixed $value): static
    {
        Arr::set($this->user, $offset, $value);

        return $this;
    }

    /**
     * Unset the given value from the raw user array.
     *
     * @param string $offset
     *
     * @return static
     */
    public function offsetUnset(string $offset): static
    {
        Arr::forget($this->user, $offset);

        return $this;
    }
}
