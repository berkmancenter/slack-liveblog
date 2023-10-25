# Slack Liveblog Wordpress plugin

## Overview

Slack Liveblog is a WordPress plugin designed to bridge the gap between your WordPress website and your Slack workspace. The plugin allows you to establish a seamless connection between a specific WordPress post or page and a corresponding Slack channel, ensuring that every message, update, or edit made in the channel is synced in real-time to the WordPress post.

### Currently supported

* New messages
* Editing messages
* Deleting messages
* Fetching images
* Fetching social media embedded elements (X, Mastodon, YouTube)

### Missing functionalities

* Threads
* Emojis
* Users avatars

## Stack
Tested on  |  Version
--|--
PHP  |  `7.4.x`
MySQL  |  > `5.7.x`
Wordpress  |  > `5.7.x`

## How to use

To create a new channel and link it to the Slack Liveblog, follow these steps:

1. Navigate to `wp-admin/admin.php?page=slack_liveblog_settings`.
2. Connect a new Slack workspace.
3. Navigate to `wp-admin/admin.php?page=slack_liveblog_channels`.
4. Enter the `Slack member ID` for the person you wish to invite to the new channel. For instructions on how to find this ID, see https://moshfeu.medium.com/how-to-find-my-member-id-in-slack-workspace-d4bba942e38c.
5. Enter a name for the new channel. Channel names can include lowercase letters, non-Latin characters, numbers, and hyphens, and must be unique in your workspace. There is a 21 character limit.
6. Click `Generate new channel`.
7. The new channel will now appear in your workspace.
8. The person you specified in `step 2` will receive an invitation to the new channel.
9. Copy the shortcode from the list (it will look something like this: `[slack_liveblog channel_id="XXXXXXXX"/]`).
10. Paste the shortcode on any post or page where you want the liveblog to appear.
11. Return to Slack, invite other users to the channel and start liveblogging.

Note that you can choose to close the channel to new updates at any time by closing it from the list of channels.

## Deployment

```
cd wp-content/plugins
git clone git@github.com:berkmancenter/slack-liveblog.git
```

Install the plugin and you are set to use it.

## Development

### 1. Set up plugin.

You will need:
* composer
* node >= 14.x
* yarn or npm

```
cd wp-content/plugins
git clone git@github.com:berkmancenter/slack-liveblog.git
cd slack-liveblog
composer install
cp .env.example .env
./build_assets.sh
cd front
yarn install
yarn run build --watch
```

### 2. Start coding!

The front-end Vue application will rebuild automatically after every code change.

## Development notes

* To print Slack events to the standard output, simply set the `SLACK_LIVEBLOG_DEBUG` environment variable to `true`. This will provide you with valuable insights and help you troubleshoot any issues that may arise.
* For multisite setups, add `--url=example.com` to `./vendor/bin/wp migrations migrate --setup` and `./vendor/bin/wp migrations migrate`. Replace `example.com` with your site's URL.

## Using WebSocket server (optional)

### Environment variables

Set these environment variables prior to running the WebSocket server.

Name  |  Description  |  Default  |  Required
--|--|--|--
`SLACK_LIVEBLOG_USE_WEBSOCKETS`  |  Tells the plugin to use the WebSocket server  |  false  |  Yes
`SLACK_LIVEBLOG_WS_SERVER_CLIENT_URL`  |  URL that clients will connect to the WebSocket server  |    |  Yes
`SLACK_LIVEBLOG_WS_SERVER_HOST`  |  Host part of the `SLACK_LIVEBLOG_WS_SERVER_CLIENT_URL`  |    |  Yes
`SLACK_LIVEBLOG_WS_SERVER_PORT`  |  Custom WebSocket server port  |  8080  |  No

### Development

During development, you can initiate the server on port `8080` by executing the command:

```
php classes/WebsocketServer.php
```

### Production

Consider using https://github.com/foreversd/forever or a similar app for optimal production performance.

## Copyright
Copyright (c) 2021 President and Fellows of Harvard College
