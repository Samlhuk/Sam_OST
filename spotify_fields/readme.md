# Spotify Fields

This is a custom Drupal 11 module that integrates with the Spotify API and provides a content type with example fields for creating Spotify Artist pages.
Navigate to /admin/config/services/spotify-fields input your Spotify Client ID and Secret. 
Add a content type of Spotify Artist, enter the Spotify Artist ID. 

## Features

- **Spotify Artist Content Type:**  
  Creates a content type called **Spotify Artist** with a dedicated field for the Spotify Artist ID.

- **Manageable Spotify Fields:**  
  Provides separate fields for:
  - **Artist Name**
  - **Artist Genres**
  - **Artist Followers**
  
  These fields are available in the Drupal Field UI and can be rearranged on both the node form and display pages.

- **Dynamic Data Updates:**  
  On saving a Spotify Artist node, the module fetches data from the Spotify API and automatically updates the corresponding fields.

- **Admin Configuration:**  
  An administration form is available at `/admin/config/services/spotify-fields` for entering your Spotify Client ID and Client Secret. 
- **Caching & Logging:**  
  The module caches the Spotify access token and logs errors to assist with troubleshooting.

