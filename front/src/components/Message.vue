<template>
  <div>
    <div class="slack-liveblog-messages-item-parent" :data-id="message.id">
      <a :id="`lbwslm-${message.id}`"></a>
      <div class="slack-liveblog-messages-item-header">
        <div v-if="shouldShowAuthor(index, messages)" class="slack-liveblog-messages-item-author">{{ message.author }}</div>
        <div class="slack-liveblog-messages-item-time">{{ formatMessageTime(message.created_at) }}</div>
        <div class="slack-liveblog-messages-item-share" @click="copyShareUrlToClipboard" title="Click to copy post link to clipboard">
          <img :src="`${window.slack_liveblog_plugin_url}/dist/front/share.svg`">
        </div>
        <Transition name="slack-liveblog-share-clipboard-fade">
          <div class="slack-liveblog-messages-item-share-copied" v-if="copiedToClipboard">Copied to clipboard</div>
        </Transition>
      </div>
      <div class="slack-liveblog-messages-item-body">
        <span v-html="message.body"></span>
        <div class="slack-liveblog-messages-item-reactions">
          <div v-for="(reaction, index) in orderedReactions(message.reactions)" class="slack-liveblog-messages-item-reaction">
            <div class="slack-liveblog-messages-item-reaction-emoji" v-html="formatUnicodeEmoji(reaction.reaction_unicode)"></div>
            <div class="slack-liveblog-messages-item-reaction-count">{{ reaction.count }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="slack-liveblog-messages-item-children">
      <Message v-for="(childMessage, childIndex) in messageChildren(message.id)" :key="childMessage.id" :message="childMessage" :messages="messages" :sorting="sorting" :index="childIndex"></Message>
    </div>
  </div>
</template>

<script>
  import orderBy from 'lodash/orderBy'

  export default {
    data() {
      return {
        order: {},
        lastLoadedTimestamp: null,
        copiedToClipboard: false,
      }
    },
    props: {
      message: {
        type: Object,
        required: true,
      },
      messages: {
        type: Array,
        required: true,
      },
      sorting: {
        type: String,
        required: true,
      },
      index: {
        type: Number,
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
    methods: {
      shouldShowAuthor(index, messagesToCheck) {
        if (index === 0) {
          // Always show the author of the first message
          return true
        }

        // Show the author if the current message is the last one
        if (index === messagesToCheck.length - 1) {
          return true
        }

        const prevMessage = messagesToCheck[index - 1]
        const currMessage = messagesToCheck[index]

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
      formatUnicodeEmoji(unicode) {
        if (unicode.includes('-')) {
          let unicodeArr = unicode.split('-')
          unicodeArr = unicodeArr.map((singleUnicode) => {
            return `&#x${singleUnicode}`
          })

          return unicodeArr.join(';')
        } else {
          return `&#x${unicode}`
        }
      },
      orderedReactions(reactions) {
        return orderBy(reactions, 'count', ['desc'])
      },
      messageChildren(parentMessageId) {
        let childrenMessages = this.messages.filter((m) => {
          if (m.parent_id === parentMessageId) {
            return true
          }
        })

        return orderBy(childrenMessages, 'created_at', [this.sorting])
      },
      copyShareUrlToClipboard() {
        window.navigator.clipboard.writeText(`${window.slack_liveblog_url}#lbwslm-${this.message.id}`)
        this.copiedToClipboard = true

        setTimeout(() => {
          this.copiedToClipboard = false
        }, 1500)
      },
    }
  }
</script>

<style lang="scss">
  $slm: ".slack-liveblog-messages";

  #{$slm}-item-padding {
    margin-bottom: 1rem;
  }

  #{$slm}-item-parent {
    margin-top: 1rem;
    position: relative;

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
    align-items: center;
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

    #{$slm}-item-reactions {
      display: flex;
      flex-wrap: wrap;

      #{$slm}-item-reaction {
        border: 1px solid #bdb4b4;
        border-radius: 5px;
        padding: 0.2rem 0.4rem;
        display: flex;
        margin-right: 0.5rem;
        margin-top: 1rem;
        width: 4rem;
        white-space: nowrap;
        justify-content: center;
        overflow: hidden;

        #{$slm}-item-reaction-count {
          margin-left: 0.5rem;
        }
      }
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
        margin: 0 !important;
      }

      .twitter-tweet + br {
        display: none;
      }
    }
  }

  #{$slm}-item-children {
    margin-left: 2rem;
  }

  #{$slm}-item-share {
    width: 1.5rem;
    height: 1.5rem;
    cursor: pointer;
    margin-left: 0.2rem;
    display: flex;
    align-items: center;
  }

  #{$slm}-item-share-copied {
    background-color: #fff;
    border-radius: 1rem;
    padding: 0.2rem;
    border: 1px solid #bdb4b4;
    font-size: 0.5em;
  }

  .slack-liveblog-share-clipboard-fade-enter-active,
  .slack-liveblog-share-clipboard-fade-leave-active {
    transition: opacity 0.5s ease;
  }

  .slack-liveblog-share-clipboard-fade-enter-from,
  .slack-liveblog-share-clipboard-fade-leave-to {
    opacity: 0;
  }
</style>
