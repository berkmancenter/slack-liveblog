const notifier = new AWN({
  icons: {
    enabled: false
  },
  position: 'top-right',
})

jQuery(document).on('click', '.slack-liveblog-channels-list-refresh-interval-save', (ev) => {
  const el = jQuery(ev.target)
  const channel_id = el.parents('tr').first().data('id')
  const value = el.parents('td').first().find('input').first().val()
  const body = ({
    action: 'slack_liveblog_admin',
    sub_action: 'update-refresh-interval',
    id: channel_id,
    refresh_interval: value,
  })

  jQuery.post(
    ajaxurl,
    body,
    () => {
      notifier.success('Refresh interval has been saved successfully.')
    }
  )
})
