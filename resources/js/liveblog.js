class Liveblog {
  #dom_el_selector;
  #dom_el;
  #ws_url;
  #ws_connection;

  constructor (dom_el_selector, ws_url) {
    this.#dom_el_selector = dom_el_selector;
    this.#dom_el = $(dom_el_selector).first();
    this.#ws_url = ws_url;
    this.#ws_connection = new WebSocket(this.#ws_url);
    let that = this;

    this.#ws_connection.onmessage = function(e) {
      let message = JSON.parse(e.data);

      switch (message['action']) {
        case 'message_new':
          that.message_new(message);
          break;
        case 'message_deleted':
          that.message_deleted(message);
          break;
        case 'message_changed':
          that.message_changed(message);
          break;
      }
    };
  }

  message_new(message) {
    let parent = $('<div/>', {
      class: 'slack-liveblog-messages-item-parent',
      'data-id': message.id
    });
    let header = $('<div/>', {
      class: 'slack-liveblog-messages-item-header'
    });

    let previous_message = this.#dom_el.find('.slack-liveblog-messages-item-parent');
    if (previous_message.length) {
      let previous_author = previous_message.find('.slack-liveblog-messages-item-author').first();

      if (previous_author.text().trim() === message.author_name) {
        previous_author.html('');
      }
    }
    let author = $('<div/>', {
      class: 'slack-liveblog-messages-item-author',
      html: message.author_name
    });

    let time = $('<div/>', {
      class: 'slack-liveblog-messages-item-time',
      html: message.created_at
    });
    let body = $('<div/>', {
      class: 'slack-liveblog-messages-item-body',
      html: message.message
    });

    header.append(author);
    header.append(time);
    parent.append(header);
    parent.append(body);
    this.#dom_el.prepend(parent);
  }

  message_deleted(message) {
    let message_dom = this.#dom_el.find(`.slack-liveblog-messages-item-parent[data-id=${message['id']}]`);

    let next_message = message_dom.next();
    if (next_message.length) {
      let next_message_author = next_message.find('.slack-liveblog-messages-item-author').first();
      let next_message_author_text = next_message_author.text().trim();
      if (!next_message_author_text) {
        let message_dom_author_text = message_dom.find('.slack-liveblog-messages-item-author').first().text().trim();
        next_message_author.text(message_dom_author_text);
      }
    }

    if (message_dom.length) {
      message_dom.remove();
    }
  }

  message_changed(message) {
    let message_dom = this.#dom_el.find(`.slack-liveblog-messages-item-parent[data-id=${message['id']}]`);

    message_dom.find('.slack-liveblog-messages-item-body').first().html(message['message']);
  }
}

$(document).ready(() => {
  if (slack_liveblog_ws_url) {
    let liveblog = new Liveblog(
      '.slack-liveblog-messages',
      slack_liveblog_ws_url
    );
  }
});
