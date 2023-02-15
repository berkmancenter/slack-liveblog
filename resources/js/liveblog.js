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
      that.render_new_message(message);
    };
  }

  render_new_message(message) {
    console.log(message);
    let parent = $('<div/>', {
      class: 'slack-liveblog-messages-item-parent'
    });
    let author = $('<div/>', {
      class: 'slack-liveblog-messages-item-author',
      html: message.author_name
    });
    let body = $('<div/>', {
      class: 'slack-liveblog-messages-item-body',
      html: message.message
    });

    parent.append(author);
    parent.append(body);
    this.#dom_el.append(parent);
  }
}

$(document).ready(() => {
  let liveblog = new Liveblog(
    '.slack-liveblog-messages',
    slack_liveblog_ws_url
  );
});
