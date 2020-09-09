<?php

namespace Drupal\webauthn;

use Drupal\Core\Config\ConfigFactoryInterface;
use Webauthn\PublicKeyCredentialRpEntity;

/**
 * RelyingParty service.
 */
class RelyingParty extends PublicKeyCredentialRpEntity {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a RelyingParty object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('webauthn.settings');
    parent::__construct(
      $this->config->get('relying_party_name'),
      $this->config->get('relying_party_id') ?: NULL,
      $this->config->get('relying_party_icon') ?: NULL
    );
  }

}
