<?php

namespace Drupal\spotify_fields\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Component\Serialization\Json;

/**
 * Service for interacting with the Spotify API.
 */
class SpotifyApiService {

  protected ClientInterface $httpClient;
  protected ConfigFactoryInterface $configFactory;
  protected CacheBackendInterface $cacheBackend;
  protected ?array $tokenData = NULL;

  const TOKEN_CACHE_KEY = 'spotify_fields.access_token';

  /**
   * Constructor.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config_factory, CacheBackendInterface $cache_backend) {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
    $this->cacheBackend = $cache_backend;
  }

  /**
   * Retrieves a valid access token.
   *
   * @return string|null
   *   The access token or NULL on failure.
   */
  public function getAccessToken(): ?string {
    // Check cache first.
    if ($cache = $this->cacheBackend->get(self::TOKEN_CACHE_KEY)) {
      $this->tokenData = $cache->data;
    }

    // If token is not present or expired, fetch a new one.
    if (empty($this->tokenData) || time() >= $this->tokenData['expires']) {
      $config = $this->configFactory->get('spotify_fields.settings');
      $client_id = $config->get('client_id');
      $client_secret = $config->get('client_secret');

      if (empty($client_id) || empty($client_secret)) {
        return NULL;
      }

      try {
        $response = $this->httpClient->request('POST', 'https://accounts.spotify.com/api/token', [
          'headers' => [
            'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
          ],
          'form_params' => [
            'grant_type' => 'client_credentials',
          ],
        ]);
        $data = Json::decode($response->getBody()->getContents());
        if (!empty($data['access_token'])) {
          $this->tokenData = [
            'token' => $data['access_token'],
            'expires' => time() + $data['expires_in'] - 10,
          ];
          // Cache the token data.
          $this->cacheBackend->set(self::TOKEN_CACHE_KEY, $this->tokenData, $this->tokenData['expires']);
        }
      }
      catch (\Exception $e) {
        \Drupal::logger('spotify_fields')->error('Error fetching access token: @message', ['@message' => $e->getMessage()]);
        return NULL;
      }
    }

    return $this->tokenData['token'] ?? NULL;
  }

  /**
   * Fetches artist data from the Spotify API.
   *
   * @param string $artist_id
   *   The Spotify artist ID.
   *
   * @return array|null
   *   An associative array of artist data or NULL on failure.
   */
  public function getArtistData(string $artist_id): ?array {
    $access_token = $this->getAccessToken();
    if (!$access_token) {
      \Drupal::logger('spotify_fields')->error('No valid access token available.');
      return NULL;
    }

    try {
      $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/artists/' . $artist_id, [
        'headers' => [
          'Authorization' => 'Bearer ' . $access_token,
        ],
      ]);
      $artist_data = Json::decode($response->getBody()->getContents());
      return $artist_data;
    }
    catch (\Exception $e) {
      \Drupal::logger('spotify_fields')->error('Error fetching artist data for ID @id: @message', [
        '@id' => $artist_id,
        '@message' => $e->getMessage(),
      ]);
      return NULL;
    }
  }
  public function searchArtists(string $query): ?array {
  $access_token = $this->getAccessToken();
  if (!$access_token) {
    \Drupal::logger('spotify_fields')->error('No valid access token available for search.');
    return NULL;
  }
  
  try {
    $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search', [
      'headers' => [
        'Authorization' => 'Bearer ' . $access_token,
      ],
      'query' => [
        'q' => $query,
        'type' => 'artist',
        'limit' => 10,
      ],
    ]);
    $data = \Drupal\Component\Serialization\Json::decode($response->getBody()->getContents());
    if (isset($data['artists']['items'])) {
      return $data['artists']['items'];
    }
  }
  catch (\Exception $e) {
    \Drupal::logger('spotify_fields')->error('Error searching for artists: @message', ['@message' => $e->getMessage()]);
  }
  return NULL;
}

}
