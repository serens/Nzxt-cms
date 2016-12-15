<?php
namespace Nzxt\Service\Auth;

use Nzxt\Model\Content\Auth\User;
use Nzxt\Service\Auth\Provider\BackendProvider;
use Nzxt\Service\Auth\Provider\ProviderInterface;

/**
 * Class AuthService
 * @package Nzxt\Service\Auth
 */
class AuthService extends \Signature\Service\AbstractInjectableService
{
    use \Signature\Object\ObjectProviderServiceTrait;
    use \Signature\Persistence\PersistenceServiceTrait;

    /**
     * @var string
     */
    protected $sessionIdentifier = 'auth.user.id';

    /**
     * @var User
     */
    protected $currentUser = null;

    /**
     * @var ProviderInterface
     */
    protected $provider = null;

    /**
     * Checks if a user is stored in the current session.
     * @return void
     */
    public function init()
    {
        parent::init();

        if (!empty($_SESSION[$this->sessionIdentifier])) {
            $userId = (int) $_SESSION[$this->sessionIdentifier];

            /** @var User $user */
            if ($user = User::find($userId)) {
                $this->currentUser = $user;
            }
        }
    }

    /**
     * Authenticates a username and password using an authentication provider.
     * @param array $authenticationInformation
     * @return User|null
     */
    public function authenticate(array $authenticationInformation)
    {
        if ($user = $this->getProvider()->authenticate($authenticationInformation)) {
            $this->setCurrentUser($user);
        }

        return $this->currentUser;
    }

    /**
     * Persists the given user by storing its user id into a session.
     * @param User $user
     * @return AuthService
     */
    public function setCurrentUser(User $user = null): AuthService
    {
        $this->currentUser = $user;

        if (null === $this->currentUser) {
            unset($_SESSION[$this->sessionIdentifier]);
        } else {
            $_SESSION[$this->sessionIdentifier] = $user->getID();
        }

        return $this;
    }

    /**
     * Returns the current registered user.
     * @return User|null
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * Returns true if an authenticated user is registered.
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return null !== $this->getCurrentUser();
    }

    /**
     * Sets the value of $provider.
     * @param ProviderInterface $provider
     * @return AuthService
     */
    public function setProvider(ProviderInterface $provider): AuthService
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Returns the value of $provider.
     * @todo Make provider configurable with configuration manager
     * @return ProviderInterface
     */
    public function getProvider(): ProviderInterface
    {
        if (null === $this->provider) {
            $this->provider = $this->objectProviderService->create(BackendProvider::class);
        }

        return $this->provider;
    }
}
