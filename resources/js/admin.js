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
        callback(response)
      }

      notifier.success(successMessage)
    },
  )
  .fail((response) => {
    notifier.alert(response.responseJSON.error ?? 'Something went wrong.')
  })
})

const slackLiveblogAdminActionsCallbacks = {
  closedChange: (response) => {
    const valueEl = jQuery('.slack-liveblog-channels-list-status')
    const linkEl = jQuery('[data-action="channel-toggle"]')
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
  }
}
