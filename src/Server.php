<?php

namespace Drupal\webauthn;

use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\Server as WebauthnServer;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Server service.
 */
class Server extends WebauthnServer {

  /**
   * Server constructor.
   *
   * @param \Drupal\webauthn\Repository $repository
   *   The public key credential source repository.
   * @param \Webauthn\PublicKeyCredentialRpEntity $relying_party
   *   The public key credential relying party entity.
   * @param \Drupal\webauthn\Metadata $metadata
   *   The metadata statement repository.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_channel_factory
   *   The logger channel factory.
   */
  public function __construct(
    Repository $repository,
    PublicKeyCredentialRpEntity $relying_party,
    Metadata $metadata,
    LoggerChannelFactory $logger_channel_factory
  ) {
    parent::__construct($relying_party, $repository, $metadata);
    $this->setLogger($logger_channel_factory->get('webauthn'));
  }

}
