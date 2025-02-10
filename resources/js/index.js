import * as Vue from "vue";
import VActivityLog from "./components/VActivityLog.vue";
import ActivityLogList from "./components/ActivityLogList.vue";
import ActivityEmail from "./components/ActivityEmail.vue";
import ActivityLogModal from "./components/ActivityLogModal.vue";
import "../sass/index.scss";

window.Vue = Vue;

const app = window.Vue.createApp({});
app.component("VActivityLog", VActivityLog);
app.component("ActivityLogList", ActivityLogList);
app.component("ActivityEmail", ActivityEmail);

app.component("ActivityLogModal", ActivityLogModal);

app.mount("#activity-log-app");
