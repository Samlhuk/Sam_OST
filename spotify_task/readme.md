# Spotify Task Module

## Overview

This module integrates with the Spotify API to allow the site administrator to configure the API credentials, create a list of Spotify artist IDs, display the list in a block, and creates pages for those artists within the Drupal site.

This module is intended to degrade gracefully, if for reason the client id or secret change the site should still operate but the artist details will no longer be fetched through the API.

This module provides:

- An **Admin Configuration Form** for entering Spotify credentials and managing the list of artist IDs.
- A **Block** to display the added artists (with clickable links if the user has permissions).
- An **Artist Detail Page** that retrieves and displays additional information (such as name, genres, images, and follower count) from Spotify.

## Configuration

Install the module.
Go to Admin > Config > Web Services > Spotify Task.
Enter Spotify Client ID and Secret and click 'Validate' to check that the details are correct.
On the same page add the artist IDs you would like to display on your site.

Go to Admin > Structure > Block layout and add a block in the region you want the block to be displayed and select the Spotify Artists block.

The block of artists should now show on your site.
