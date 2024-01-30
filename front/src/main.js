import { createApp } from 'vue'
import App from './App.vue'

document.addEventListener("DOMContentLoaded", function() {
  const app = createApp(App)

  app.config.globalProperties.window = window

  app.mount('#slack-liveblog-app')
});
