<?php

namespace Drupal\webauthn;

use Drupal\Core\Config\ConfigFactoryInterface;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * Repository service.
 */
class Repository implements PublicKeyCredentialSourceRepository {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a Repository object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource {
    // TODO: Implement findOneByCredentialId() method.
  }

  /**
   * {@inheritdoc}
   */
  public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array {
    // TODO: Implement findAllForUserEntity() method.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void {
    // TODO: Implement saveCredentialSource() method.
  }

}
