=== Liveblog with Slack ===
Contributors: berkmancenter
Donate link: https://cyber.harvard.edu
Tags: slack, blog
Requires at least: 5.7
Tested up to: 5.7
Stable tag: 0.3.0
Requires PHP: 7.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Liveblog with Slack connects your WordPress website and your Slack workspace. Sync up a post or page to a Slack channel!

== Description ==

Liveblog with Slack is a WordPress plugin designed to bridge the gap between your WordPress website and your Slack workspace. The plugin allows you to establish a seamless connection between a specific WordPress post or page and a corresponding Slack channel, ensuring that every message, update, or edit made in the channel is synced in real-time to the WordPress post.

Currently Supported:

* New messages
* Editing messages
* Deleting messages
* Fetching images
* Fetching social media embedded elements (X, Mastodon, YouTube)

Missing functionalities:

* Threads
* Users avatars

== Installation ==

To create a new channel and link it to the Liveblog with Slack, follow these steps:

1. Navigate to `wp-admin/admin.php?page=slack_liveblog_settings`.
1. Connect a new Slack workspace.
1. Navigate to `wp-admin/admin.php?page=slack_liveblog_channels`.
1. Enter the `Slack member ID` for the person you wish to invite to the new channel. For instructions on how to find this ID, see [here](https://moshfeu.medium.com/how-to-find-my-member-id-in-slack-workspace-d4bba942e38c).
1. Enter a name for the new channel. Channel names can include lowercase letters, non-Latin characters, numbers, and hyphens, and must be unique in your workspace. There is a 21 character limit.
1. Click `Generate new channel`.
1. The new channel will now appear in your workspace.
1. The person you specified earlier will receive an invitation to the new channel.
1. Copy the shortcode from the list (it will look something like this: `[slack_liveblog channel_id="XXXXXXXX"/]`).
1. Paste the shortcode on any post or page where you want the live blog to appear.
1. Return to Slack, invite other users to the channel and start live blogging.

Note that you can choose to close the channel to new updates at any time by closing it from the list of channels.

To *optionally* use the WebSocket server, set these enviornment variables:

* `SLACK_LIVEBLOG_USE_WEBSOCKETS`
** **Description:** Tells the plugin to use the WebSocket server
** **Default:** false
** **Required:** Yes
* `SLACK_LIVEBLOG_WS_SERVER_CLIENT_URL`
** **Description:** URL that clients will connect to the WebSocket server
** **Default:**
** **Required:** Yes
* `SLACK_LIVEBLOG_WS_SERVER_HOST`
** **Description:** Host part of the `SLACK_LIVEBLOG_WS_SERVER_CLIENT_URL`
** **Default:**
** **Required:** Yes
* `SLACK_LIVEBLOG_WS_SERVER_PORT`
** **Description:** Custom WebSocket server port
** **Default:** 8080
** **Required:** No

== Frequently Asked Questions ==

= Why can't I see emojis and images in the live blog view? =

Currently, we only support plain text messages, so emojis and images won't display.

= What are the channel name limitations? =

Channel names can include lowercase letters, non-Latin characters, numbers, and hyphens, but must be unique within your workspace and have a 21-character limit.

= How can I invite others to the newly created channel? =

After generating the new channel, return to Slack, and invite other users to the channel directly from Slack.

== Screenshots ==

1. An example of how the live blog appears. Names of users, timestamps, and messages.
2. The settings page for the plugin. Add a new Workspace and Slack access token or view existing Workspace connections.
3. The page for managing channels. Generate a new channel or manage existing ones here.

== Changelog ==

= 0.3.0 =
* Support for images, embedded objects, and emojis.
* Optimized the XHR version of the front-end app to minimize data traffic.
* Enabled the ability to set a refresh interval of liveblog messages.
* Allowed to set of a delay for message publishing in the liveblog app.

= 0.2.1 =
* Started using Wordpress UI elements in the admin panel.

= 0.2.0 =
* Allowed to connect to multiple Slack workspaces.
* Made it easier to use, without shell access and technical knowledge.

= 0.1.0 =
* Basic functionality (synced adding, editing, removing messages).
