# Slack Liveblog Wordpress plugin

## Overview

Slack Liveblog is a WordPress plugin designed to bridge the gap between your WordPress website and your Slack workspace. The plugin allows you to establish a seamless connection between a specific WordPress post or page and a corresponding Slack channel, ensuring that every message, update, or edit made in the channel is synced in real-time to the WordPress post.

### Currently supported

* New messages
* Editing messages
* Deleting messages

### Missing functionalities

* Threads
* Emojis
* Images
* Users avatars

## Stack
Tested on  |  Version
--|--
PHP  |  `7.4.x`
MySQL  |  `5.7.x` and `8.0.x`
Wordpress  |  `5.7.x`

## Installation & deployment

### 1. Create a new Slack application.

To initiate the creation of a new Slack application, proceed to the URL https://api.slack.com/apps?new_app=1 and follow the instructions to complete the process. A convenient and efficient technique is to import a manifest file from the GitHub repository located at https://github.com/berkmancenter/slack-liveblog/blob/main/slack_app_manifest.json.

It is crucial to note that, during this process, the placeholder `YOUR_SITE_URL` must be replaced with the correct URL for your website.

Be sure to make note of the following values from your Slack app's configuration page:
* `Verification Token` from the `Basic Information` page
* `Bot User OAuth Token` from the `OAuth & Permissions` page

These values are needed for the proper functioning of the plugin, and you will need to reference them during the configuration process.

### 2. Set up plugin.

You will need:
* composer
* node >= 14.x
* yarn or npm

```
cd wp-content/plugins
git clone git@github.com:berkmancenter/slack-liveblog.git
cd slack-liveblog
composer install
./vendor/bin/wp migrations migrate --setup
./vendor/bin/wp migrations migrate
cp .env.example .env
cd front
yarn install
yarn run build
```

#### Environment variables

If you prefer, you can also configure these settings in the plugin settings on your WordPress dashboard. This will prevent users from seeing the authentication values or modifying them inadvertently.

Name  |  Description  |  Default  |  Required
--|--|--|--
`SETTINGS_FORM_FIELD_API_AUTH_TOKEN`  |  Value of the `Bot User OAuth Token`  |    |  No
`SLACK_LIVEBLOG_CHECKBOX_FIELD_API_SIGNING_SECRET`  |  Value of the `Verification Token`  |    |  No
`SLACK_LIVEBLOG_CHECKBOX_FIELD_TEAM_HOME`  |  Url to your slack workspace (e.g. `https://harvard.slack.com`)  |    |  No

Notes:
* For multisite setups, add `--url=example.com` to `./vendor/bin/wp migrations migrate --setup` and `./vendor/bin/wp migrations migrate`. Replace `example.com` with your site's URL.

### 3. Start a websocket server.

#### Development

During development, you can initiate the server on port `8080` by executing the command:

```
php classes/WebsocketServer.php
```

#### Production

Consider using https://github.com/foreversd/forever or a similar app for optimal production performance.

#### Environment variables

Name  |  Description  |  Default  |  Required
--|--|--|--
`WS_SERVER_CLIENT_URL`  |  URL that clients will connect to the WebSocket server  |    |  Yes
`WS_SERVER_HOST`  |  Host part of the `WS_SERVER_CLIENT_URL`  |    |  Yes
`WS_SERVER_PORT`  |  Custom WebSocket server port  |  8080  |  No

## Using

To create a new channel and link it to the Slack Liveblog, follow these steps:

1. Navigate to `wp-admin/admin.php?page=slack_liveblog_channels`.
2. Enter the `Slack member ID` for the person you wish to invite to the new channel. For instructions on how to find this ID, see https://moshfeu.medium.com/how-to-find-my-member-id-in-slack-workspace-d4bba942e38c.
3. Enter a name for the new channel. Channel names can include lowercase letters, non-Latin characters, numbers, and hyphens, and must be unique in your workspace. There is a 21 character limit.
4. Click `Generate new channel`.
5. The new channel will now appear in your workspace.
6. The person you specified in `step 2` will receive an invitation to the new channel.
7. Copy the shortcode from the list (it will look something like this: `[slack_liveblog channel_id="XXXXXXXX"/]`).
8. Paste the shortcode on any post or page where you want the liveblog to appear.
9. Return to Slack, invite other users to the channel and start liveblogging.

Note that you can choose to close the channel to new updates at any time by closing it from the list of channels.

## Development notes

* To print Slack events to the standard output, simply set the `SLACK_LIVEBLOG_DEBUG` environment variable to `true`. This will provide you with valuable insights and help you troubleshoot any issues that may arise.

## Copyright
Copyright (c) 2021 President and Fellows of Harvard College
