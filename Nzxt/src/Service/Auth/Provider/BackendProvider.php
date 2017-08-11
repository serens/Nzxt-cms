<?php
namespace Nzxt\Service\Auth\Provider;

use Nzxt\Model\Content\Auth\User;

/**
 * Class BackendProvider
 * @package Nzxt\Service\Auth\Provider
 */
class BackendProvider implements ProviderInterface
{
    use \Signature\Object\ObjectProviderServiceTrait;

    /**
     * Authenticates against the database.
     * @param array $authenticationInformation
     * @return User|null
     * @throws \InvalidArgumentException In case authentication information is in wrong format
     */
    public function authenticate(array $authenticationInformation)
    {
        foreach (['username', 'password'] as $validKey) {
            if (!array_key_exists($validKey, $authenticationInformation)) {
                throw new \InvalidArgumentException(
                    'Argument $authenticationInformation must have key "' . $validKey . '".'
                );
            }
        }

        $persistenceService = $this->objectProviderService->get('PersistenceService');

        $result = User::findByQuery('*', sprintf(
            'username=%s AND active=1',
            $persistenceService->quote($authenticationInformation['username'])
        ));

        if ($result && $result->count()) {
            $candidate = $result->getFirst();

            if (password_verify($authenticationInformation['password'], $candidate->getFieldValue('password'))) {
                return $candidate;
            }
        }

        return null;
    }
}
