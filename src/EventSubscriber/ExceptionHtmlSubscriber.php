<?php

namespace Drupal\exception\EventSubscriber;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\EventSubscriber\CustomPageExceptionHtmlSubscriber;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Url;
use Drupal\exception\Form\ExceptionsSettingsForm;
use Drupal\onlyone\OnlyOne;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * Exception subscriber for handling core custom HTML error pages.
 */
class ExceptionHtmlSubscriber extends CustomPageExceptionHtmlSubscriber {

  /**
   * The only one service.
   *
   * @var OnlyOne
   */
  protected OnlyOne $onlyOne;

  /**
   * The language manager.
   *
   * @var LanguageManager
   */
  protected LanguageManager $languageManager;

  /**
   * Constructs a new CustomPageExceptionHtmlSubscriber.
   *
   * @param ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param HttpKernelInterface $http_kernel
   *   The HTTP Kernel service.
   * @param LoggerInterface $logger
   *   The logger service.
   * @param RedirectDestinationInterface $redirect_destination
   *   The redirect destination service.
   * @param UrlMatcherInterface $access_unaware_router
   *   A router implementation which does not check access.
   * @param AccessManagerInterface $access_manager
   *   The access manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, HttpKernelInterface $http_kernel, LoggerInterface $logger, RedirectDestinationInterface $redirect_destination, UrlMatcherInterface $access_unaware_router, AccessManagerInterface $access_manager, OnlyOne $onlyOne, LanguageManager $languageManager) {
    parent::__construct($config_factory, $http_kernel, $logger, $redirect_destination, $access_unaware_router, $access_manager);
    $this->onlyOne = $onlyOne;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  protected static function getPriority() {
    return -49;
  }

  /**
   * {@inheritdoc}
   */
  public function on40x(ExceptionEvent $event) {

    $config = $this->configFactory->get(ExceptionsSettingsForm::CONFIGNAME);
    $contentType = $config->get(ExceptionsSettingsForm::CLIENT_ERROR);

    if (empty($contentType)) {
      parent::on4xx($event);
    }

    $match = $this->onlyOne->existsNodesContentType($contentType, $this->languageManager->getCurrentLanguage()->getId());
    if (!$match) {
      parent::on4xx($event);
    }

    $route = Url::fromRoute('entity.node.canonical', ['node' => $match])->toString();
    $route = $route instanceof GeneratedUrl ? $route->getGeneratedUrl() : $route;
    $this->makeSubrequestToCustomPath($event, $route, Response::HTTP_FORBIDDEN);
  }

  /**
   * {@inheritdoc}
   */
  public function on403(ExceptionEvent $event) {

    $config = $this->configFactory->get(ExceptionsSettingsForm::CONFIGNAME);
    $contentType = $config->get(ExceptionsSettingsForm::ACCESS_DENIED);

    if (empty($contentType)) {
      parent::on4xx($event);
    }

    $match = $this->onlyOne->existsNodesContentType($contentType, $this->languageManager->getCurrentLanguage()->getId());
    if (!$match) {
      parent::on403($event);
    }

    $route = Url::fromRoute('entity.node.canonical', ['node' => $match])->toString();
    $route = $route instanceof GeneratedUrl ? $route->getGeneratedUrl() : $route;
    $this->makeSubrequestToCustomPath($event, $route, Response::HTTP_FORBIDDEN);
  }

  /**
   * {@inheritdoc}
   */
  public function on404(ExceptionEvent $event) {

    $config = $this->configFactory->get(ExceptionsSettingsForm::CONFIGNAME);
    $contentType = $config->get(ExceptionsSettingsForm::NOT_FOUND);

    if (empty($contentType)) {
      parent::on4xx($event);
    }

    $match = $this->onlyOne->existsNodesContentType($contentType, $this->languageManager->getCurrentLanguage()->getId());
    if (!$match) {
      parent::on404($event);
    }

    $route = Url::fromRoute('entity.node.canonical', ['node' => $match])->toString();
    $route = $route instanceof GeneratedUrl ? $route->getGeneratedUrl() : $route;
    $this->makeSubrequestToCustomPath($event, $route, Response::HTTP_NOT_FOUND);
  }

}
