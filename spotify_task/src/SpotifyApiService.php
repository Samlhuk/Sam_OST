<?php

namespace Drupal\spotify_task;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Component\Datetime\Time;
use Psr\Log\LoggerInterface;

/**
 * Provides an interface to the Spotify API.
 */
class SpotifyApiService {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\Time
   */
  protected $time;

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a SpotifyApiService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   * @param \Drupal\Component\Datetime\Time $time
   *   The time service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ClientInterface $httpClient, Time $time, LoggerInterface $logger) {
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
    $this->time = $time;
    $this->logger = $logger;
  }

  /**
   * Ensures a valid access token is available.
   *
   * @return string|null
   *   The valid access token, or NULL on failure.
   */
  public function ensureValidAccessToken() {
    $config = $this->configFactory->get('spotify_task.settings');
    $access_token = $config->get('access_token');
    $token_expires = $config->get('token_expires');
    $current_time = $this->time->getRequestTime();

    // If the token is missing or expired, attempt to refresh it.
    if (!$access_token || $current_time >= $token_expires) {
      $client_id = $config->get('client_id');
      $client_secret = $config->get('client_secret');
      if (empty($client_id) || empty($client_secret)) {
        return NULL;
      }

      try {
        $response = $this->httpClient->post('https://accounts.spotify.com/api/token', [
          'form_params' => ['grant_type' => 'client_credentials'],
          'headers' => [
            'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
            'Content-Type' => 'application/x-www-form-urlencoded',
          ],
        ]);
        $data = json_decode($response->getBody(), TRUE);
        if (!empty($data['access_token'])) {
          $access_token = $data['access_token'];
          $expires_in = $data['expires_in'];
          $token_expires = $current_time + $expires_in;
          // Update configuration with the new token details.
          $this->configFactory->getEditable('spotify_task.settings')
            ->set('access_token', $access_token)
            ->set('token_expires', $token_expires)
            ->save();
          return $access_token;
        }
        else {
          return NULL;
        }
      }
      catch (\Exception $e) {
        $this->logger->error('Failed to refresh Spotify access token: @error', ['@error' => $e->getMessage()]);
        return NULL;
      }
    }
    return $access_token;
  }

  /**
   * Fetches artist details from Spotify.
   *
   * @param string $artist_id
   *   The Spotify artist ID.
   *
   * @return array|null
   *   The artist details, or NULL if the fetch fails.
   */
  public function fetchArtistDetails($artist_id) {
    $access_token = $this->ensureValidAccessToken();
    if (!$access_token) {
      return NULL;
    }
    try {
      $response = $this->httpClient->get("https://api.spotify.com/v1/artists/$artist_id", [
        'headers' => ['Authorization' => "Bearer $access_token"],
      ]);
      return json_decode($response->getBody(), TRUE);
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to fetch artist details: @error', ['@error' => $e->getMessage()]);
      return NULL;
    }
  }

}
