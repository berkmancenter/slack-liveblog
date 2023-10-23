<template>
  <div class="slack-liveblog-messages">
    <div v-for="(message, index) in messages" :key="message.id" class="slack-liveblog-messages-item-parent" :data-id="message.id">
      <div class="slack-liveblog-messages-item-header">
        <div v-if="shouldShowAuthor(index)" class="slack-liveblog-messages-item-author">{{ message.author }}</div>
        <div class="slack-liveblog-messages-item-time">{{ formatMessageTime(message.created_at) }}</div>
      </div>
      <div class="slack-liveblog-messages-item-body">
        <span v-html="message.body"></span>
      </div>
    </div>
  </div>
</template>

<script>
  import orderBy from 'lodash/orderBy'

  export default {
    data() {
      return {
        messages: [],
        order: {},
      }
    },
    props: {
      wsUrl: {
        type: String,
        required: true,
      },
      messagesUrl: {
        type: String,
        required: true,
      },
      closed: {
        type: Boolean,
        required: true,
      },
      useWebsockets: {
        type: Boolean,
        required: false,
      },
      refreshInterval: {
        type: Boolean,
        required: true,
      },
      sorting: {
        type: String,
        required: true,
      },
    },
    computed: {
      formatMessageTime() {
        return function (created_at) {
          if (!created_at) {
            return ''
          }

          const date = new Date(Date.parse(created_at))
          return date.toLocaleString('en-US', { timeZone: 'America/New_York', hour12: true, hour: 'numeric', minute: 'numeric' })
        }
      },
    },
    mounted() {
      const that = this
      this.loadMessages()

      if (this.closed === '1') {
        return
      }

      if (this.useWebsockets) {
        this.initWebSocket()
      } else {
        setInterval(() => that.loadMessages(), that.refreshInterval)
      }
    },
    methods: {
      initWebSocket() {
        const socket = new WebSocket(this.wsUrl)
        socket.onmessage = (event) => {
          const message = JSON.parse(event.data)
          switch (message.action) {
            case 'message_new':
              this.addMessage(message)
              break
            case 'message_deleted':
              this.deleteMessage(message)
              break
            case 'message_changed':
              this.updateMessage(message)
              break
          }
        }
      },
      loadMessages() {
        fetch(this.messagesUrl)
          .then(response => response.json())
          .then(data => {
            this.messages = orderBy(data, 'created_at', [this.sorting])
          })
          .catch(error => console.error(error))
      },
      addMessage(message) {
        this.messages.unshift(message)
      },
      deleteMessage(message) {
        this.messages = this.messages.filter(m => m.id !== message.id)
      },
      updateMessage(message) {
        const index = this.messages.findIndex(m => m.id === message.id)
        if (index !== -1) {
          this.messages[index]['body'] = message.message
        }
      },
      shouldShowAuthor(index) {
        if (index === 0) {
          // Always show the author of the first message
          return true
        }

        // Show the author if the current message is the last one
        if (index === this.messages.length - 1) {
          return true
        }

        const prevMessage = this.messages[index - 1]
        const currMessage = this.messages[index]

        const prevAuthor = prevMessage && prevMessage.author
        const currAuthor = currMessage && currMessage.author
        const prevCreatedAt = prevMessage && prevMessage.created_at && Date.parse(prevMessage.created_at)
        const currCreatedAt = currMessage && currMessage.created_at && Date.parse(currMessage.created_at)

        // Show the author if the previous message was written by a different author
        if (prevAuthor !== currAuthor) {
          return true
        }

        // Show the author if the previous message was written more than 10 minutes ago
        let timeDiff
        if (this.sorting === 'desc') {
          timeDiff = currCreatedAt && prevCreatedAt ? prevCreatedAt - currCreatedAt : 0
        } else {
          timeDiff = currCreatedAt && prevCreatedAt ? currCreatedAt - prevCreatedAt : 0
        }
        const minutesDiff = timeDiff / (1000 * 60)

        if (minutesDiff >= 10) {
          return true
        }

        // Otherwise, don't show the author
        return false
      },
    }
  }
</script>

<style lang="scss">
  $slm: ".slack-liveblog-messages";

  #{$slm} * {
    box-sizing: border-box;
  }

  #{$slm}-item-padding {
    margin-bottom: 1rem;
  }

  #{$slm}-item-parent {
    margin-top: 1rem;

    &:first-child {
      margin-top: 0;
    }
  }

  #{$slm}-item-author {
    font-weight: bold;
    font-size: 110%;
  }

  #{$slm}-item-header {
    display: flex;
    align-items: baseline;
  }

  #{$slm}-item-body {
    width: 100%;
    background-color: #ffffff;
    border-radius: 1rem;
    padding: 1rem;
    border: 1px solid #bdb4b4;
    overflow-wrap: anywhere;
    word-break: normal;

    img,
    #{$slm}-embedded-items-item {
      display: block;
      max-width: 100%;
      padding: 1rem;
      border-radius: 1rem;
      border: 1px solid #bdb4b4;
      margin-bottom: 1rem;
      background-color: #f0f8fc;
    }
  }

  #{$slm}-item-time {
    margin-left: 1rem;
  }

  #{$slm}-embedded-items {
    margin-top: 0.5rem;

    #{$slm}-embedded-items-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
      background-color: #f0f8fc;
      margin-bottom: 1rem;

      &:last-child {
        margin-bottom: 0;
      }

      .twitter-tweet {
        margin: 0;
      }

      .twitter-tweet + br {
        display: none;
      }
    }
  }
</style>
