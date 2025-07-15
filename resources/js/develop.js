import "../sass/develop.scss";
import "../sass/index.scss";

// develop.js
import { createApp } from "vue";
import VActivityLog from "./components/VActivityLog.vue";
import ActivityLogList from "./components/ActivityLogList.vue";
import ActivityEmail from "./components/ActivityEmail.vue";
import ActivityLogModal from "./components/ActivityLogModal.vue";
import ActivityLogDeleteComment from "./components/ActivityLogDeleteComment.vue";

import { createI18n } from "vue-i18n";
import mitt from "mitt";

const messages = {
  en: window.__translations || {},
};

const i18n = createI18n({
  legacy: true,
  locale: "en",
  fallbackLocale: "en",
  messages,
});

const emitter = mitt();
emitter.$on = emitter.on;
emitter.$off = emitter.off;
emitter.$emit = emitter.emit;

document.addEventListener("DOMContentLoaded", () => {
  const app = createApp({});

  app.use(i18n);
  app.provide("bus", emitter);

  app.component("v-activity-log", VActivityLog);
  app.component("activity-log-list", ActivityLogList);
  app.component("activity-email", ActivityEmail);
  app.component("activity-log-modal", ActivityLogModal);
  app.component("activity-log-delete-comment", ActivityLogDeleteComment);

  app.mount("#activity-log-app");
});
