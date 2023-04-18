const notifier = new AWN({
  icons: {
    enabled: false
  },
  position: 'top-right',
})

jQuery(document).on('click', '.slack-liveblog-ajax-action', (ev) => {
  ev.preventDefault();

  const el = jQuery(ev.target)
  const elementsToSubmit = jQuery(el.data('elements-submit'))
  const successMessage = el.data('success-message')
  const successCallback = el.data('success-callback')
  const subAction = el.data('action')
  let body = ({
    action: 'slack_liveblog_admin',
    sub_action: subAction,
  })

  elementsToSubmit.each((_index, element) => {
    const elSubmit = jQuery(element)

    body[elSubmit.data('key')] = elSubmit.val()
  })

  jQuery.post(
    ajaxurl,
    body,
    (response) => {
      const callback = slackLiveblogAdminActionsCallbacks[successCallback]

      if (callback) {
        callback(response, body)
      }

      notifier.success(successMessage)
    },
  )
  .fail((response) => {
    notifier.alert(response.responseJSON.error ?? 'Something went wrong.')
  })
})

const slackLiveblogAdminActionsCallbacks = {
  closedChange: (response, body) => {
    const parent = jQuery(`tr[data-id="${body.id}"]`)
    const valueEl = parent.find(`.slack-liveblog-channels-list-status-${body.id}`).first()
    const linkEl = parent.find('[data-action="channel-toggle"]').first()
    const currentStatusEl = linkEl.prev()
    const updatedValue = valueEl.val()

    if (updatedValue === '1') {
      valueEl.val('0')
      linkEl.text('Open')
      currentStatusEl.text('Yes')
    } else {
      valueEl.val('1')
      linkEl.text('Close')
      currentStatusEl.text('No')
    }
  },
  createdWorkspace: (response) => {
    window.location.href = response.redirect_url
  },
  createdChannel: (response) => {
    const body = ({
      action: 'slack_liveblog_admin',
      sub_action: 'channels-list',
    })

    jQuery.post(
      ajaxurl,
      body,
      (response) => {
        jQuery('.slack-liveblog-channels-parent').html(response)
      },
    )
  },
}
