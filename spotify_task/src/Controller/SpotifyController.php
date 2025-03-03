<?php

namespace Drupal\spotify_task\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\spotify_task\SpotifyApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles the artist details page.
 */
class SpotifyController extends ControllerBase {

  /**
   * The Spotify API service.
   *
   * @var \Drupal\spotify_task\SpotifyApiService
   */
  protected $spotifyApi;

  /**
   * Constructs a new SpotifyController object.
   *
   * @param \Drupal\spotify_task\SpotifyApiService $spotifyApi
   *   The Spotify API service.
   */
  public function __construct(SpotifyApiService $spotifyApi) {
    $this->spotifyApi = $spotifyApi;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('spotify_task.spotify_api')
    );
  }

  /**
   * Displays the Spotify artist details.
   *
   *
   * @param string $artist_id
   *   The Spotify artist ID.
   *
   * @return array
   *   A render array.
   */
  public function artistDetails($artist_id) {
    $config = $this->config('spotify_task.settings');
    $open_access = $config->get('open_access');

    if (!$open_access) {
      $artists = $config->get('artists') ?: [];
      $allowed = FALSE;
      foreach ($artists as $artist) {
        if ($artist['artist_id'] == $artist_id) {
          $allowed = TRUE;
          break;
        }
      }
      if (!$allowed) {
        return [
          '#markup' => $this->t('This artist is not available for public viewing.'),
        ];
      }
    }

    // Fetch artist details using the Spotify API service.
    $artist = $this->spotifyApi->fetchArtistDetails($artist_id);
    if (!$artist) {
      return [
        '#markup' => $this->t('Failed to fetch artist details or no valid access token.'),
      ];
    }

    return [
      '#theme' => 'spotify_artist',
      '#artist' => $artist,
    ];
  }

  /**
   * Returns a title for the artist details page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return string
   *   The page title.
   */
  public static function getArtistTitle(Request $request) {
    return 'Artist Details';
  }

}
