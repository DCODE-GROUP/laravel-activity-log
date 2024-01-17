import * as Vue from "vue";
window.Vue = Vue;

import VActivityLog from "./components/VActivityLog.vue";
import ActivityLogList from "./components/ActivityLogList.vue";
import ActivityEmail from "./components/ActivityEmail.vue";
import "../sass/index.scss";

const app = window.Vue.createApp({});
app.component("VActivityLog", VActivityLog);
app.component("ActivityLogList", ActivityLogList);
app.component("ActivityEmail", ActivityEmail);

app.mount('#activity-log-app');