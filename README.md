# Sam_OST

Summary

Module Name: spotify_task

Purpose:
The module integrates with the Spotify API to allow administrators to configure API credentials, and manage a list of Spotify artist IDs. 
It also provides a block for listing the added artists and an artist details page that fetches and displays additional information from Spotify.

Key Components:

    Spotify API Service:
        Centralises all interactions with the Spotify API.
        Manages token generation and automatic refresh when expired.

    Admin Configuration Form:
        Provides a form to allow administrators to input and validate Spotify API credentials.
        Provides a second field group for managing a table of artist IDs.
        Enforces a limit of 20 artists and prevents duplicate entries.

    Block:
        Displays a list of added artists.
        Optionally renders artist names as clickable links if the user has permission to view the detailed artist page.

    Artist Detail Page:
        Fetches and displays detailed information for a given Spotify artist ID.
        Uses the Spotify API Service to retrieve data from Spotify, including artist genres, images, and follower counts.
        Provides a custom Twig template for output.

Workflow:

    Enters your Spotify Client ID and Client Secret on the admin form.
    Add upto 20 artist IDs.
    Add and configure the Spotify Block.
    The artist detail page uses the Spotify API Service to fetch and display artist details.

Demo can be seen at https://spotify.samlh.co.uk/
Demo user details, 
user: Test_Ac9a
pass: Fpz}g5LL+5ie~;R
