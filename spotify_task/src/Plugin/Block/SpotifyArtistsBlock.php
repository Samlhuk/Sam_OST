<?php

namespace Drupal\spotify_task\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\spotify_task\SpotifyApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Spotify Artists Block.
 *
 * @Block(
 *   id = "spotify_artists_block",
 *   admin_label = @Translation("Spotify Artists")
 * )
 */
class SpotifyArtistsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  
  /**
   * The Spotify API service.
   *
   * @var \Drupal\spotify_task\SpotifyApiService
   */
  protected $spotifyApi;

  /**
   * Constructs a new SpotifyArtistsBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the block.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \Drupal\spotify_task\SpotifyApiService $spotifyApi
   *   The Spotify API service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, SpotifyApiService $spotifyApi) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->spotifyApi = $spotifyApi;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('spotify_task.spotify_api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
  $current_user = \Drupal::currentUser();
  $can_view_details = $current_user->hasPermission('view spotify artists');
  
  $config = $this->configFactory->get('spotify_task.settings');
  $artists = $config->get('artists') ?? [];
  
  if (empty($artists)) {
    return ['#markup' => $this->t('No artists added yet.')];
  }
  
  $items = [];
  foreach ($artists as $artist) {
    if ($can_view_details) {
      // Render as a clickable link.
      $items[] = [
        '#type' => 'link',
        '#title' => $artist['artist_name'],
        '#url' => \Drupal\Core\Url::fromRoute('spotify_task.artist', ['artist_id' => $artist['artist_id']]),
      ];
    }
    else {
      // Render as plain text.
      $items[] = $artist['artist_name'];
    }
  }
  
  return ['#theme' => 'item_list', '#items' => $items];
}

}
