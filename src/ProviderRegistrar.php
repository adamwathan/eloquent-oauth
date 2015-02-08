<?php namespace AdamWathan\EloquentOAuth;

use AdamWathan\EloquentOAuth\Providers\ProviderInterface;
use AdamWathan\EloquentOAuth\Exceptions\ProviderNotRegisteredException;

class ProviderRegistrar
{
    private $providers = array();

    public function registerProvider($alias, ProviderInterface $provider)
    {
        $this->providers[$alias] = $provider;
    }

    public function getProvider($providerAlias)
    {
        if (! $this->hasProvider($providerAlias)) {
            throw new ProviderNotRegisteredException("No provider has been registered under the alias '{$providerAlias}'");
        }
        return $this->providers[$providerAlias];
    }

    protected function hasProvider($alias)
    {
        return isset($this->providers[$alias]);
    }
}
