<?php

namespace Drupal\webauthn;

use Base64Url\Base64Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * The Drupal Public Key Credential Source Repository implementation.
 *
 * @package Drupal\webauthn
 */
class DrupalPublicKeyCredentialSourceRepository implements PublicKeyCredentialSourceRepository {

  const DATABASE_TABLE = 'webauthn_public_key_credential_source';

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * DrupalPublicKeyCredentialSourceRepository constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Connection $database) {
    $this->configFactory = $config_factory;
    $this->database = $database;
  }

  /**
   * Find by credential id.
   *
   * @param string $publicKeyCredentialId
   *   The public key credential.
   *
   * @return \Webauthn\PublicKeyCredentialSource|null
   *   The public key credential source.
   */
  public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource {
    $query = $this->database
      ->select(static::DATABASE_TABLE, 'c');
    $query
      ->condition('c.cid', Base64Url::encode($publicKeyCredentialId), '=')
      ->fields('c', ['cid', 'uuid', 'credential']);
    $result = $query
      ->execute();
    if ($record = $result->fetch()) {
      return PublicKeyCredentialSource::createFromArray(json_decode($record->credential, TRUE));
    }
    return NULL;
  }

  /**
   * Find all for user entity.
   *
   * @param \Webauthn\PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity
   *   The public key credential user entity.
   *
   * @return \Webauthn\PublicKeyCredentialSource[]
   *   An array of public key credential sources.
   */
  public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array {
    $sources = [];
    $rows = $this->database
      ->select(static::DATABASE_TABLE, 'c')
      ->fields('c', ['cid', 'uuid', 'credential'])
      ->condition('uuid', $publicKeyCredentialUserEntity->getId(), '=')
      ->execute();
    foreach ($rows as $record) {
      $sources[] = PublicKeyCredentialSource::createFromArray(json_decode($record->credential, TRUE));
    }
    return $sources;
  }

  /**
   * Save credential source.
   *
   * @param \Webauthn\PublicKeyCredentialSource $publicKeyCredentialSource
   *   The public key credential source.
   *
   * @throws \Exception
   */
  public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void {
    $this->database
      ->insert(static::DATABASE_TABLE)
      ->fields([
        'cid' => Base64Url::encode($publicKeyCredentialSource->getPublicKeyCredentialId()),
        'uuid' => $publicKeyCredentialSource->getUserHandle(),
        'credential' => json_encode($publicKeyCredentialSource),
      ])
      ->execute();
  }

}
