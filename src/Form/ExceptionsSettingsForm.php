<?php

namespace Drupal\exception\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\onlyone\OnlyOne;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ExceptionsSettingsForm
 */
class ExceptionsSettingsForm extends ConfigFormBase {

  public const CONFIGNAME  = 'exception.settings';

  public const CLIENT_ERROR  = '40x';
  public const ACCESS_DENIED  = '403';
  public const NOT_FOUND  = '404';

  /**
   * @var OnlyOne
   */
  private OnlyOne $onlyOne;

  /**
   * @param ConfigFactoryInterface $config_factory
   * @param OnlyOne $onlyOne
   */
  public function __construct(ConfigFactoryInterface $config_factory, OnlyOne $onlyOne)
  {
    parent::__construct($config_factory);
    $this->onlyOne = $onlyOne;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {

    /** @var OnlyOne $onlyOne */
    $onlyOne = $container->get('onlyone');

    /** @var ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');

    return new static(
      $config_factory,
      $onlyOne
    );
  }

  /**
   * @inheritdoc
   */
  protected function getEditableConfigNames() {
    return [self::CONFIGNAME];
  }

  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIGNAME);

    $options = [];
    $contentTypes = $this->onlyOne->getAvailableContentTypes();
    foreach ($contentTypes as $contentType) {
      $options[$contentType] = $contentType;
    }

    $form[self::CLIENT_ERROR] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t('(Only one) Content type to use for 40X error page.'),
      '#description' => $this->t('The (Only one) Content type to redirect to in case of a 40X error'),
      '#default_value' => $config->get(self::CLIENT_ERROR),
    ];

    $form[self::ACCESS_DENIED] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t('(Only one) Content type to use for 403 error page.'),
      '#description' => $this->t('The (Only one) Content type to redirect to in case of a 403 error'),
      '#default_value' => $config->get(self::ACCESS_DENIED),
    ];

    $form[self::NOT_FOUND] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t('(Only one) Content type to use for 404 error page.'),
      '#description' => $this->t('The (Only one) Content type to redirect to in case of a 403 error'),
      '#default_value' => $config->get(self::NOT_FOUND),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * @inheritdoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config(self::CONFIGNAME);

    $config->set(self::CLIENT_ERROR, $form_state->getValue(self::CLIENT_ERROR));
    $config->set(self::ACCESS_DENIED, $form_state->getValue(self::ACCESS_DENIED));
    $config->set(self::NOT_FOUND, $form_state->getValue(self::NOT_FOUND));
    $config->save();
  }

  /**
   * @inheritdoc
   */
  public function getFormId() {
    return 'exceptions_settings_form';
  }
}
