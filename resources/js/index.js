import * as Vue from "vue";
window.Vue = Vue;

import ActivityLogList from "./components/ActivityLogList.vue";
import ActivityEmail from "./components/ActivityEmail.vue";
import "../sass/index.pcss";

const app = window.Vue.createApp({});
app.component("ActivityLogList", ActivityLogList);
app.component("ActivityEmail", ActivityEmail);

app.mount('#activity-log-app');