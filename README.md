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



Next Steps
This module was built as a test in 2 hours and provides a form for adding artist IDs, a block which displays the artist name for those IDs and a page for each of those artists. 

Some improvements which could be considered given more time include;
A form for the block, such as which fields should be shown, Artist image, artist name etc. 
A form for the artist page, so fields can be toggled on/off or re-arranged. 
Better logging/debugging such as including if the API fails, client id and secret are missing or invalid.
A way to throttle data, or manage data in case the API gets rate limited, such as saving data and fetching new data if available.
A test file could also be provided. 


An alternative approach which I looked at was using Drupals in built content types for each page and using manageable fields. 
I created a demo at https://spotify-fields.samlh.co.uk, using 
user: test_am7an 
pass: KqCMm7GH":3gbH

For this demo I created a module which creates a content type called Spotify Artist, which accepts the artist ID. All the fields are manageable fields which can be arranged or disabled under manage display for the content type. As this utilises Drupals core content type and core fields, it is compatible with modules such as views and layout builder, so blocks can be created of selected artists under views, and drupals inbuilt permissions can be used to control who can see the block/view. 
A feature I did not include which I would have looked in to if given more time is to link the artists genres with taxonomy. 
