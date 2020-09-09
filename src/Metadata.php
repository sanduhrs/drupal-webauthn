<?php

namespace Drupal\webauthn;

use Drupal\Core\Config\ConfigFactoryInterface;
use Webauthn\MetadataService\MetadataStatement;
use Webauthn\MetadataService\MetadataStatementRepository;

/**
 * Metadata service.
 */
class Metadata implements MetadataStatementRepository {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a Metadata object.
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
  public function findOneByAAGUID(string $aaguid): ?MetadataStatement {
    // TODO: Implement findOneByAAGUID() method.
  }

  /**
   * {@inheritdoc}
   */
  public function findStatusReportsByAAGUID(string $aaguid): array {
    // TODO: Implement findStatusReportsByAAGUID() method.
  }

}
