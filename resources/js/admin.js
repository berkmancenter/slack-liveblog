const notifier = new AWN({
  icons: {
    enabled: false
  },
  position: 'top-right',
})

jQuery(document).on('click', '.slack-liveblog-channels-list-action', (ev) => {
  const el = jQuery(ev.target)
  const parentTr = el.parents('tr').first()
  const channelId = parentTr.first().data('id')
  const elementsToSubmit = parentTr.find(el.data('elements-submit'))
  const successMessage = el.data('success-message')
  const successCallback = el.data('success-callback')
  const subAction = el.data('action')
  let body = ({
    action: 'slack_liveblog_admin',
    sub_action: subAction,
    id: channelId,
  })

  elementsToSubmit.each((_index, element) => {
    const elSubmit = jQuery(element)

    body[elSubmit.data('key')] = elSubmit.val()
  })

  jQuery.post(
    ajaxurl,
    body,
    () => {
      const callback = slackLiveblogAdminActionsCallbacks[successCallback]

      if (callback) {
        callback()
      }

      notifier.success(successMessage)
    },
  )
})

const slackLiveblogAdminActionsCallbacks = {
  closedChange: () => {
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
  }
}
