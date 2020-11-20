<?php

namespace Drupal\webauthn;

use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\Server;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * The Drupal Webauthn Server implementation.
 *
 * @package Drupal\webauthn
 */
class DrupalWebauthnServer extends Server {

  /**
   * DrupalWebauthnServer constructor.
   *
   * @param \Drupal\webauthn\DrupalPublicKeyCredentialSourceRepository $repository
   *   The public key credential source repository.
   * @param \Webauthn\PublicKeyCredentialRpEntity $relying_party
   *   The public key credential relying party entity.
   * @param \Drupal\webauthn\DrupalMetadataStatementRepository $metadata
   *   The metadata statement repository.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_channel_factory
   *   The logger channel factory.
   */
  public function __construct(
    DrupalPublicKeyCredentialSourceRepository $repository,
    PublicKeyCredentialRpEntity $relying_party,
    DrupalMetadataStatementRepository $metadata,
    LoggerChannelFactory $logger_channel_factory
  ) {
    parent::__construct($relying_party, $repository, $metadata);

    if (!\Drupal::config('webauthn.settings')->get('development')) {
      $this->setLogger($logger_channel_factory->get('webauthn'));
    }
  }

}
