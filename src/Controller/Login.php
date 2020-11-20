<?php

namespace Drupal\webauthn\Controller;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\webauthn\DrupalPublicKeyCredentialUserEntityRepository;
use GuzzleHttp\Psr7\ServerRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\Server;

/**
 * The login controller implementation.
 *
 * @package Drupal\webauthn\Controller
 */
class Login extends ControllerBase {

  /**
   * The configuration object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

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
   * @var \Webauthn\PublicKeyCredentialSourceRepository
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
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannel|\Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The uuid service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_channel_factory
   *   The logger channel factory.
   * @param \Webauthn\Server $webauthn_server
   *   The Drupal Webauthn server.
   * @param \Webauthn\PublicKeyCredentialSourceRepository $public_key_credential_source_repository
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
      PublicKeyCredentialSourceRepository $public_key_credential_source_repository,
      DrupalPublicKeyCredentialUserEntityRepository $public_key_credential_user_entity_repository
  ) {
    $this->config = $config_factory->get('webauthn.settings');
    $this->uuid = $uuid;
    $this->request = $request_stack->getCurrentRequest();
    $this->logger = $logger_channel_factory->get('webauthn');
    $this->webauthnServer = $webauthn_server;
    $this->publicKeyCredentialSourceRepository = $public_key_credential_source_repository;
    $this->publicKeyCredentialUserEntityRepository = $public_key_credential_user_entity_repository;

    if ($this->config->get('development')) {
      $this->webauthnServer
        ->setSecuredRelyingPartyId(['localhost']);
    }
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
   * Login options callback.
   */
  public function options() {
    $content = json_decode($this->request->getContent());

    // UserEntity found using the username.
    $user_entity = $this->publicKeyCredentialUserEntityRepository
      ->findWebauthnUserByUsername($content->username);

    // Get the list of authenticators associated to the user.
    $credential_sources = $this->publicKeyCredentialSourceRepository
      ->findAllForUserEntity($user_entity);

    // Convert the Credential Sources into Public Key Credential Descriptors.
    $allowed_credentials = array_map(function (PublicKeyCredentialSource $credential) {
      return $credential->getPublicKeyCredentialDescriptor();
    }, $credential_sources);

    // Generate the set of request options.
    $public_key_credential_request_options = $this->webauthnServer
      ->generatePublicKeyCredentialRequestOptions(
        PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
        $allowed_credentials
      );

    // Temporarily store user and credentials in the session variable.
    $_SESSION['webauthn'] = [
      'public_key_credential_request_options' => serialize($public_key_credential_request_options),
      'public_key_credentials_user_entity' => serialize($user_entity),
    ];

    return new JsonResponse($public_key_credential_request_options, 200);
  }

  /**
   * Action callback.
   */
  public function action() {
    // Get user and credentials from the session variable.
    /** @var \Webauthn\PublicKeyCredentialRequestOptions $public_key_credential_request_options */
    $public_key_credential_request_options = unserialize($_SESSION['webauthn']['public_key_credential_request_options']);
    $user_entity = unserialize($_SESSION['webauthn']['public_key_credentials_user_entity']);
    unset($_SESSION['webauthn']);

    try {
      $this->webauthnServer
        ->loadAndCheckAssertionResponse(
          \Drupal::request()->getContent(),
          $public_key_credential_request_options,
          $user_entity,
          ServerRequest::fromGlobals()
        );

      // If everything is fine, this means the user has correctly
      // been authenticated.
      $this->publicKeyCredentialUserEntityRepository
        ->login($user_entity);
    }
    catch (\Throwable $exception) {
      $this->logger->error('Could not login: @error', ['@error' => $exception->getMessage()]);
      return new JsonResponse(NULL, 400);
    }
    return new JsonResponse(NULL, 200);
  }

}
