<?php

namespace Drupal\webauthn;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\user\Entity\User;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * The Drupal Public Key Credential User Entity Repository implementation.
 *
 * @package Drupal\webauthn
 */
class DrupalPublicKeyCredentialUserEntityRepository {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * DrupalPublicKeyCredentialSourceRepository constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Find Webauthn user by username.
   *
   * @param string $username
   *   The username string.
   *
   * @return \Webauthn\PublicKeyCredentialUserEntity|null
   *   The public key credential user entity or null.
   */
  public function findWebauthnUserByUsername(string $username): ?PublicKeyCredentialUserEntity {
    try {
      /** @var \Drupal\user\Entity\User[] $users */
      $users = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->loadByProperties([
         'name' => $username,
       ]);
    }
    catch (\Throwable $exception) {
      \Drupal::logger('webauthn')->error('Could not find user by name: @error', ['error' => $exception->getMessage()]);
      return NULL;
    }
    return $users ? $this->createUserEntity(reset($users)) : NULL;
  }

  /**
   * Find Webauthn user by user handle.
   *
   * @param string $userHandle
   *   The user handle (id).
   *
   * @return \Webauthn\PublicKeyCredentialUserEntity|null
   *   The public key credential user entity or null.
   */
  public function findWebauthnUserByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity {
    try {
      /** @var \Drupal\user\Entity\User[] $users */
      $users = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->load($userHandle);
    }
    catch (\Throwable $exception) {
      \Drupal::logger('webauthn')->error('Could not find user by handle: @error', ['error' => $exception->getMessage()]);
      return NULL;
    }
    return $users ? $this->createUserEntity(reset($users)) : NULL;
  }

  /**
   * Create user entity.
   *
   * @param \Drupal\user\Entity\User $user
   *   The Drupal user entity.
   *
   * @return \Webauthn\PublicKeyCredentialUserEntity
   *   The public key credential user entity.
   */
  private function createUserEntity(User $user): PublicKeyCredentialUserEntity {
    return new PublicKeyCredentialUserEntity(
      $user->getAccountName(),
      $user->uuid(),
      $user->getDisplayName(),
      NULL
    );
  }

  /**
   * Save a new user.
   *
   * @param \Webauthn\PublicKeyCredentialUserEntity $userEntity
   *   The public key credential user entity.
   */
  public function save(PublicKeyCredentialUserEntity $userEntity) {
    try {
      User::create([
        'uuid' => $userEntity->getId(),
        'name' => $userEntity->getName(),
        'status' => 1,
      ])->save();
    }
    catch (\Throwable $exception) {
      \Drupal::logger('webauthn')->error('Could not save user: @error', ['error' => $exception->getMessage()]);
    }
  }

  /**
   * Login an existing user.
   *
   * @param \Webauthn\PublicKeyCredentialUserEntity $userEntity
   *   The public key credential user entity.
   */
  public function login(PublicKeyCredentialUserEntity $userEntity) {
    try {
      /** @var \Drupal\user\Entity\User[] $users */
      $users = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->loadByProperties([
          'uuid' => $userEntity->getId(),
        ]);
      user_login_finalize(reset($users));
    }
    catch (\Throwable $exception) {
      \Drupal::logger('webauthn')->error('Could not login user: @error', ['error' => $exception->getMessage()]);
    }
  }

}
