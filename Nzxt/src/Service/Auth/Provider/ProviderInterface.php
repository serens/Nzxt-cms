<?php
namespace Nzxt\Service\Auth\Provider;

/**
 * Class ProviderService
 * @package Nzxt\Service\Auth\Provider
 */
interface ProviderInterface
{
    /**
     * @param array $authenticationInformation
     * @return \Nzxt\Model\Content\Auth\User
     */
    public function authenticate(array $authenticationInformation);
}
