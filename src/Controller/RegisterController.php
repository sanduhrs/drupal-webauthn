<?php

namespace Drupal\webauthn\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * Returns responses for Webauthn routes.
 */
class RegisterController extends ControllerBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The controller constructor.
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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Action callback.
   */
  public function action() {
    $content = json_decode(\Drupal::request()->getContent());
    return new JsonResponse([], 200);
  }

  /**
   * Register options callback.
   */
  public function options() {
    $content = json_decode(\Drupal::request()->getContent());

    /** @var \Drupal\webauthn\Server $server */
    $server = \Drupal::service('webauthn.server');
    $server->setSecuredRelyingPartyId([
      'localhost',
      '127.0.0.1',
    ]);

    $userEntity = new PublicKeyCredentialUserEntity(
      $content->username,
      'ea4e7b55-d8d0-4c7e-bbfa-78ca96ec574c',
      $content->displayName
    );

    $publicKeyCredentialCreationOptions = $server
      ->generatePublicKeyCredentialCreationOptions($userEntity);
    $data = $publicKeyCredentialCreationOptions->jsonSerialize();
    return new JsonResponse($data);
  }

}
