<template>
  <div class="slack-liveblog-messages">
    <Message v-for="(message, index) in parentMessages" :key="message.id" :message="message" :messages="messages" :sorting="sorting" :index="index"></Message>
  </div>
</template>

<script>
  import orderBy from 'lodash/orderBy'
  import uniqBy from 'lodash/uniqBy'
  import Message from './Message.vue'
  import { nextTick } from 'vue'

  export default {
    components: {
      Message,
    },
    data() {
      return {
        messages: [],
        order: {},
        lastLoadedTimestamp: null,
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
        type: Number,
        required: true,
      },
      sorting: {
        type: String,
        required: true,
      },
    },
    computed: {
      parentMessages() {
        return this.messages.filter((m) => {
          if (!m.parent_id) {
            return true
          }
        })
      }
    },
    mounted() {
      this.initialLoadMessages()

      if (this.closed === '1') {
        return
      }

      if (this.useWebsockets) {
        this.initWebSocket()
      } else {
        setInterval(
          () => { this.loadMessagesUpdates() },
          this.refreshInterval
        )
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
      initialLoadMessages() {
        this.lastLoadedTimestamp = Date.now()

        fetch(this.messagesUrl)
          .then(response => response.json())
          .then(data => {
              this.messages = orderBy(data.new, 'created_at', [this.sorting])
              this.jumpToSpecificMessage()
          })
          .catch(error => console.error(error))
      },
      loadMessagesUpdates() {
        const apiUrl = `${this.messagesUrl}&from=${this.lastLoadedTimestamp}`

        fetch(apiUrl)
          .then(response => response.json())
          .then(data => {
            const incomingMessages = data.new
            const updatedMessages = data.updated
            const deletedMessages = data.deleted

            if (incomingMessages.length > 0) {
              const newMessages = orderBy(uniqBy([...this.messages, ...incomingMessages], 'id'), 'created_at', [this.sorting])
              this.messages = newMessages
            }

            if (updatedMessages.length > 0) {
              updatedMessages.forEach(message => {
                this.updateMessage(message)
              })
            }

            if (deletedMessages.length > 0) {
              deletedMessages.forEach(message => {
                this.deleteMessage(message)
              })
            }

            this.lastLoadedTimestamp += this.refreshInterval
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
          this.messages[index] = message
        }
      },
      async jumpToSpecificMessage() {
        if (window.location.hash) {
          await nextTick()
          const requestedHash = window.location.hash.slice(1)
          window.location.hash = ''
          window.location.hash = requestedHash
        }
      },
    }
  }
</script>

<style lang="scss">
  $slm: ".slack-liveblog-messages";

  #{$slm} * {
    box-sizing: border-box;
  }
</style>
