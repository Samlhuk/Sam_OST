<?php

namespace Drupal\spotify_task\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\spotify_task\SpotifyApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Spotify Admin Form.
 */
class SpotifyAdminForm extends ConfigFormBase {

  /**
   * The Spotify API service.
   *
   * @var \Drupal\spotify_task\SpotifyApiService
   */
  protected $spotifyApi;

  /**
   * Constructs a new SpotifyAdminForm.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager
   *   The typed config manager.
   * @param \Drupal\spotify_task\SpotifyApiService $spotifyApi
   *   The Spotify API service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TypedConfigManagerInterface $typed_config_manager, SpotifyApiService $spotifyApi) {
    parent::__construct($config_factory, $typed_config_manager);
    $this->spotifyApi = $spotifyApi;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('spotify_task.spotify_api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'spotify_task_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['spotify_task.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load stored configuration.
    $config = $this->config('spotify_task.settings');

    // ============================================================
    // Field Group 1: Spotify API Credentials.
    // ============================================================
    $form['api_credentials'] = [
      '#type' => 'details',
      '#title' => $this->t('Spotify API Credentials'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['api_credentials']['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify Client ID'),
      '#default_value' => $config->get('client_id') ?: '',
      '#required' => TRUE,
    ];
    $form['api_credentials']['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify Client Secret'),
      '#default_value' => $config->get('client_secret') ?: '',
      '#required' => TRUE,
    ];
    $form['api_credentials']['validate_credentials'] = [
      '#type' => 'submit',
      '#value' => $this->t('Validate Credentials & Generate Token'),
      '#submit' => ['::validateCredentials'],
      '#ajax' => [
        'callback' => '::ajaxCredentialsCallback',
        'wrapper' => 'spotify-credentials-wrapper',
      ],
    ];
    $form['api_credentials']['credentials_message'] = [
      '#type' => 'markup',
      '#markup' => $form_state->get('credentials_message') ? $form_state->get('credentials_message') : '',
      '#prefix' => '<div id="spotify-credentials-wrapper">',
      '#suffix' => '</div>',
    ];

    // ============================================================
    // Field Group 2: Manage Spotify Artists.
    // ============================================================
    $form['artist_management'] = [
      '#type' => 'details',
      '#title' => $this->t('Manage Spotify Artists'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['artist_management']['artist_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify Artist ID'),
      '#default_value' => '',
    ];
    $form['artist_management']['add_artist'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Artist'),
      '#submit' => ['::addArtist'],
      '#ajax' => [
        'callback' => '::ajaxArtistCallback',
        'wrapper' => 'spotify-artists-wrapper',
      ],
    ];
    $form['artist_management']['artist_message'] = [
      '#type' => 'markup',
      '#markup' => $form_state->get('artist_message') ? $form_state->get('artist_message') : '',
    ];
    $artists = $config->get('artists') ?: [];
    $header = [
      'artist_id' => $this->t('Artist ID'),
      'artist_name' => $this->t('Artist Name'),
      'operations' => $this->t('Operations'),
    ];
    $form['artist_management']['artists'] = [
      '#type' => 'table',
      '#header' => $header,
      '#attributes' => ['id' => 'spotify-artists-wrapper'],
      '#empty' => $this->t('No artists added.'),
    ];
    foreach ($artists as $key => $artist) {
      $form['artist_management']['artists'][$key]['artist_id'] = [
        '#plain_text' => $artist['artist_id'],
      ];
      $form['artist_management']['artists'][$key]['artist_name'] = [
        '#plain_text' => $artist['artist_name'],
      ];
      $form['artist_management']['artists'][$key]['operations'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#name' => 'remove_artist_' . $key,
        '#submit' => ['::removeArtist'],
        '#ajax' => [
          'callback' => '::ajaxArtistCallback',
          'wrapper' => 'spotify-artists-wrapper',
        ],
        '#limit_validation_errors' => [],
      ];
    }

    // ============================================================
    // Field Group 3: Artist Access Settings.
    // ============================================================
    $form['access_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Artist Access Settings'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['access_settings']['open_access'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow any artist to be viewed'),
      '#default_value' => $config->get('open_access') ? 1 : 0,
      '#description' => $this->t('If checked, any artist ID can be accessed at spotify/artist/[artistID]. If not, only artists added via this form are accessible.'),
    ];
    $form['access_settings']['save_access_settings'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Access Settings'),
      '#submit' => ['::saveAccessSettings'],
      '#ajax' => [
        'callback' => '::ajaxAccessSettingsCallback',
        'wrapper' => 'access-settings-wrapper',
      ],
    ];
    $form['access_settings']['access_settings_message'] = [
      '#type' => 'markup',
      '#markup' => $form_state->get('access_settings_message') ? $form_state->get('access_settings_message') : '',
      '#prefix' => '<div id="access-settings-wrapper">',
      '#suffix' => '</div>',
    ];

    // Remove the default configuration form save button.
    $form = parent::buildForm($form, $form_state);
    unset($form['actions']['submit']);

    return $form;
  }

  /**
   * Submit handler to validate credentials and generate an access token.
   */
  public function validateCredentials(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('api_credentials');
    $client_id = $values['client_id'];
    $client_secret = $values['client_secret'];

    // Save credentials in configuration.
    $this->configFactory->getEditable('spotify_task.settings')
      ->set('client_id', $client_id)
      ->set('client_secret', $client_secret)
      ->save();

    // Use the service to ensure a valid access token.
    $access_token = $this->spotifyApi->ensureValidAccessToken();
    if ($access_token) {
      $message = $this->t('Access token generated successfully.');
    }
    else {
      $message = $this->t('Invalid Client ID or Secret.');
    }
    $form_state->set('credentials_message', $message);
    $form_state->setRebuild();
  }

  /**
   * AJAX callback for credentials validation.
   */
  public function ajaxCredentialsCallback(array &$form, FormStateInterface $form_state) {
    return $form['api_credentials']['credentials_message'];
  }

  /**
   * Submit handler to add an artist.
   */
  public function addArtist(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('artist_management');
    $artist_id = trim($values['artist_id']);

    if (empty($artist_id)) {
      $form_state->set('artist_message', $this->t('Artist ID cannot be empty.'));
      $form_state->setRebuild();
      return;
    }

    // Use the service to fetch artist details.
    $artist_details = $this->spotifyApi->fetchArtistDetails($artist_id);
    if (!$artist_details || empty($artist_details['name'])) {
      $form_state->set('artist_message', $this->t('Invalid Artist ID or unable to fetch artist details.'));
      $form_state->setRebuild();
      return;
    }

    $artist_name = $artist_details['name'];

    // Retrieve the current artists list from configuration.
    $config = $this->config('spotify_task.settings');
    $artists = $config->get('artists') ?: [];

    // Check if maximum allowed (20) is reached.
    if (count($artists) >= 20) {
      $form_state->set('artist_message', $this->t('You have reached the maximum number of artists allowed (20).'));
      $form_state->setRebuild();
      return;
    }

    // Check if this artist is already added.
    foreach ($artists as $artist) {
      if ($artist['artist_id'] === $artist_id) {
        $form_state->set('artist_message', $this->t('Artist already added.'));
        $form_state->setRebuild();
        return;
      }
    }

    // Add the new artist.
    $artists[] = [
      'artist_id' => $artist_id,
      'artist_name' => $artist_name,
    ];
    $this->configFactory->getEditable('spotify_task.settings')
      ->set('artists', $artists)
      ->save();

    $form_state->set('artist_message', $this->t('Artist %name added successfully.', ['%name' => $artist_name]));
    $form_state->setValue(['artist_management', 'artist_id'], '');
    $form_state->setRebuild();
  }

  /**
   * Submit handler to remove an artist.
   */
  public function removeArtist(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $name = $trigger['#name'];
    $key = str_replace('remove_artist_', '', $name);

    $config = $this->config('spotify_task.settings');
    $artists = $config->get('artists') ?: [];

    if (isset($artists[$key])) {
      unset($artists[$key]);
      $artists = array_values($artists);
      $this->configFactory->getEditable('spotify_task.settings')
        ->set('artists', $artists)
        ->save();
      $form_state->set('artist_message', $this->t('Artist removed successfully.'));
    }
    else {
      $form_state->set('artist_message', $this->t('Artist not found.'));
    }
    $form_state->setRebuild();
  }

  /**
   * AJAX callback for artist add/remove actions.
   */
  public function ajaxArtistCallback(array &$form, FormStateInterface $form_state) {
    return $form['artist_management']['artists'];
  }

  /**
   * Submit handler to save the access settings.
   */
  public function saveAccessSettings(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('access_settings');
    $open_access = !empty($values['open_access']) ? 1 : 0;

    $this->configFactory->getEditable('spotify_task.settings')
      ->set('open_access', $open_access)
      ->save();

    $form_state->set('access_settings_message', $this->t('Access settings saved.'));
    $form_state->setRebuild();
  }

  /**
   * AJAX callback for access settings.
   */
  public function ajaxAccessSettingsCallback(array &$form, FormStateInterface $form_state) {
    return $form['access_settings']['access_settings_message'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

}
