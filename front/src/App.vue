<template>
  <div id="slack-liveblog">
    <h2>Live Blog</h2>
    <live-blog :wsUrl="wsUrl" :messagesUrl="messagesUrl" />
  </div>
</template>

<script>
import LiveBlog from './components/LiveBlog.vue';

export default {
  name: 'SlackLiveblog',
  components: {
    LiveBlog
  },
  data() {
    return {
      wsUrl: window.slack_liveblog_ws_url,
      messagesUrl: window.slack_liveblog_messages_url
    }
  }
}
</script>

<style scoped lang="scss">
  #slack-liveblog {
    h2 {
      margin-bottom: 1rem;
    }
  }
</style>
