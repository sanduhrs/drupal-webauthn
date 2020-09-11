<?php

namespace Drupal\webauthn;

use Drupal\Core\Config\ConfigFactoryInterface;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * The Drupal Public Key Credential Source Repository implementation.
 *
 * @package Drupal\webauthn
 */
class FilePublicKeyCredentialSourceRepository implements PublicKeyCredentialSourceRepository {

  private $path = '/tmp/pubkey-repo.json';

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

  public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
  {
    $data = $this->read();
    if (isset($data[base64_encode($publicKeyCredentialId)]))
    {
      return PublicKeyCredentialSource::createFromArray($data[base64_encode($publicKeyCredentialId)]);
    }
    return null;
  }

  /**
   * @return PublicKeyCredentialSource[]
   */
  public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
  {
    $sources = [];
    foreach($this->read() as $data)
    {
      $source = PublicKeyCredentialSource::createFromArray($data);
      if ($source->getUserHandle() === $publicKeyCredentialUserEntity->getId())
      {
        $sources[] = $source;
      }
    }
    return $sources;
  }

  public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
  {
    $data = $this->read();
    $data[base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId())] = $publicKeyCredentialSource;
    $this->write($data);
  }

  private function read(): array
  {
    if (file_exists($this->path))
    {
      return json_decode(file_get_contents($this->path), true);
    }
    return [];
  }

  private function write(array $data): void
  {
    if (!file_exists($this->path))
    {
      if (!mkdir($concurrentDirectory = dirname($this->path), 0700, true) && !is_dir($concurrentDirectory)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
      }
    }
    file_put_contents($this->path, json_encode($data), LOCK_EX);
  }

}
