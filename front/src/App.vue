<template>
  <div id="slack-liveblog">
    <h2>Live Blog</h2>
    <live-blog 
      :wsUrl="wsUrl"
      :messagesUrl="messagesUrl"
      :closed="closed"
      :useWebsockets="useWebsockets"
      :refreshInterval="refreshInterval"
      :sorting="sorting"
    ></live-blog>
  </div>
</template>

<script>
import LiveBlog from './components/LiveBlog.vue'

export default {
  name: 'SlackLiveblog',
  components: {
    LiveBlog,
  },
  data() {
    return {
      wsUrl: window.slack_liveblog_ws_url,
      messagesUrl: window.slack_liveblog_messages_url,
      closed: window.slack_liveblog_closed,
      useWebsockets: window.slack_liveblog_use_websockets,
      refreshInterval: parseInt(window.slack_liveblog_refresh_interval),
      sorting: window.slack_liveblog_sorting,
    }
  },
}
</script>

<style scoped lang="scss">
  #slack-liveblog {
    h2 {
      margin-bottom: 1rem;
    }
  }
</style>
