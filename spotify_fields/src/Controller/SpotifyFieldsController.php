<?php

namespace Drupal\spotify_fields\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\spotify_fields\Service\SpotifyApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for handling Spotify artist autocomplete.
 */
class SpotifyFieldsController extends ControllerBase {

  /**
   * The Spotify API service.
   *
   * @var \Drupal\spotify_fields\Service\SpotifyApiService
   */
  protected $spotifyApiService;

  /**
   * Constructs a new SpotifyFieldsController.
   *
   * @param \Drupal\spotify_fields\Service\SpotifyApiService $spotify_api_service
   *   The Spotify API service.
   */
  public function __construct(SpotifyApiService $spotify_api_service) {
    $this->spotifyApiService = $spotify_api_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('spotify_fields.spotify_api')
    );
  }

  /**
   * Autocomplete callback for the artist field.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object containing the query string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing suggestion items.
   */
  public function artistAutocomplete(Request $request) {
    $results = [];
    $string = $request->query->get('q');
    if ($string) {
      $artists = $this->spotifyApiService->searchArtists($string);
      if (!empty($artists)) {
        foreach ($artists as $artist) {
          $results[] = [
            // The value stored in the field will be the artist's ID.
            'value' => $artist['id'],
            // The label shown to the user includes the artist's name and ID.
            'label' => $artist['name'] . ' (' . $artist['id'] . ')',
          ];
        }
      }
    }
    return new JsonResponse($results);
  }
}
