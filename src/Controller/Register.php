<?php

namespace Drupal\webauthn\Controller;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\webauthn\DrupalPublicKeyCredentialSourceRepository;
use Drupal\webauthn\DrupalPublicKeyCredentialUserEntityRepository;
use GuzzleHttp\Psr7\ServerRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\Server;

/**
 * The registration controller implementation.
 *
 * @package Drupal\webauthn\Controller
 */
class Register extends ControllerBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The uuid service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * The Webauthn server.
   *
   * @var \Webauthn\Server
   */
  protected $webauthnServer;

  /**
   * The public key credential source repository.
   *
   * @var \Drupal\webauthn\DrupalPublicKeyCredentialSourceRepository
   */
  protected $publicKeyCredentialSourceRepository;

  /**
   * The public key credential user entity repository.
   *
   * @var \Drupal\webauthn\DrupalPublicKeyCredentialUserEntityRepository
   */
  protected $publicKeyCredentialUserEntityRepository;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * @var \Drupal\Core\Logger\LoggerChannel|\Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Component\Uuid\UuidInterface
   *   The uuid service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_channel_factory
   * @param \Webauthn\Server $webauthn_server
   *   The Drupal Webauthn server
   * @param \Drupal\webauthn\DrupalPublicKeyCredentialSourceRepository $public_key_credential_source_repository
   *   The public key credential source repository.
   * @param \Drupal\webauthn\DrupalPublicKeyCredentialUserEntityRepository $public_key_credential_user_entity_repository
   *   The public key credential user entity repository.
   */
  public function __construct(
      ConfigFactoryInterface $config_factory,
      UuidInterface $uuid,
      RequestStack $request_stack,
      LoggerChannelFactory $logger_channel_factory,
      Server $webauthn_server,
      DrupalPublicKeyCredentialSourceRepository $public_key_credential_source_repository,
      DrupalPublicKeyCredentialUserEntityRepository $public_key_credential_user_entity_repository
  ) {
    $this->configFactory = $config_factory;
    $this->uuid = $uuid;
    $this->request = $request_stack->getCurrentRequest();
    $this->logger = $logger_channel_factory->get('webauthn');
    $this->webauthnServer = $webauthn_server;
    $this->publicKeyCredentialSourceRepository = $public_key_credential_source_repository;
    $this->publicKeyCredentialUserEntityRepository = $public_key_credential_user_entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('uuid'),
      $container->get('request_stack'),
      $container->get('logger.factory'),
      $container->get('webauthn.server'),
      $container->get('webauthn.public_key_credential_source_repository'),
      $container->get('webauthn.public_key_credential_user_entity_repository')
    );
  }

  /**
   * Register options callback.
   */
  public function options() {
    $content = json_decode($this->request->getContent());

    $user_entity = new PublicKeyCredentialUserEntity(
      $content->username,
      $this->uuid->generate(),
      $content->displayName
    );

    $public_key_credential_creation_options = $this->webauthnServer
      ->generatePublicKeyCredentialCreationOptions($user_entity);

    // Temporarily store user and credentials in the session variable.
    $_SESSION['webauthn'] = [
      'public_key_credential_creation_options' => serialize($public_key_credential_creation_options),
      'public_key_credentials_user_entity' => serialize($user_entity),
    ];

    $data = $public_key_credential_creation_options
      ->jsonSerialize();
    return new JsonResponse($data);
  }

  /**
   * Action callback.
   */
  public function action() {
    // Get user and credentials from the session variable.
    $public_key_credential_creation_options = unserialize($_SESSION['webauthn']['public_key_credential_creation_options']);
    $user_entity = unserialize($_SESSION['webauthn']['public_key_credentials_user_entity']);
    unset($_SESSION['webauthn']);

    $this->webauthnServer
      ->setSecuredRelyingPartyId(['localhost']);

    try {
      $public_key_credential_source = $this->webauthnServer
        ->loadAndCheckAttestationResponse(
          \Drupal::request()->getContent(),
          $public_key_credential_creation_options,
          ServerRequest::fromGlobals()
        );

      // The user entity and the public key credential source can now be stored
      // using their repository.
      $this->publicKeyCredentialSourceRepository
        ->saveCredentialSource($public_key_credential_source);

      // If you create a new user account, you should also save the user entity.
      $this->publicKeyCredentialUserEntityRepository
        ->save($user_entity);
    }
    catch(\Throwable $exception) {
      $this->logger->error('Could not register: @error', ['@error' => $exception->getMessage()]);
      return new JsonResponse([], 400);
    }
    return new JsonResponse([], 200);
  }

}
