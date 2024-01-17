<template>
  <table class="min-w-full border-separate border-spacing-y-2 text-sm font-medium text-gray-900">
    <caption class="py-3 text-left font-semibold">
      {{
        $t("activity-log.headings.title")
      }}
    </caption>
    <colgroup>
      <col class="w-2/12" />
      <col class="w-6/12" />
      <col class="w-2/12" />
      <col class="w-2/12" />
    </colgroup>
    <tbody class="">
      <tr :key="activity.id" v-for="activity in activities">
        <td class="bg-slate-50 py-4 pl-3">
          {{ activity.meta ? activity.meta.action : $t("activity-log.update_entity") }}
        </td>
        <td class="whitespace-nowrap bg-slate-50 py-4">
          <div class="">
            <v-icon
              v-if="activity.communication"
              classes="w-5 h-5 mr-xsSpace inline cursor-pointer hover:text-blue-400"
              icon="EnvelopeIcon"
              @click="openModal(activity)"
            ></v-icon>
            <span v-html="activity.description"></span>
          </div>
        </td>
        <td class="bg-slate-50 py-4">
          {{ activity?.meta?.created_by_label || $t("activity-log.labels.created_by") }}:
          {{ activity?.meta?.created_by || activity.user }}
        </td>
        <td class="bg-slate-50 px-3 py-4 text-right">
          {{ activity.created_at_date }}
        </td>
      </tr>
    </tbody>
  </table>
</template>
<script>
import axios from "axios";

export default {
  name: "ActivityLogList",
  inject: ["bus"],
  props: {
    getUrl: {
      type: String,
      default: "/api/generic/activity-logs",
    },
    modelClass: {
      type: String,
      required: true,
    },
    modelId: {
      type: [String, Number],
      required: true,
    },
    allowComment: {
      type: Boolean,
      default: false,
    },
    refreshSelf: {
      type: Boolean,
      default: false,
    },
    currentUser: {
      type: String,
      default: "Guest",
    },
    modalEvent: {
      type: String,
      default: 'openModal',
    },
    reloadEvent: {
      type: String,
      default: 'getActivities',
    }
  },
  data() {
    return {
      loading: false,
      comment: null,
      activities: [],
    };
  },
  mounted() {
    this.bus.$on(this.reloadEvent, () => {
      this.getActivityLog();
    });
    this.getActivityLog();
  },
  methods: {
    openModal(activity) {
      this.bus.$emit(this.modalEvent, {
        componentName: "ActivityEmail",
        componentData: {
          content: activity.communication.content,
          to: activity.communication.to,
          subject: activity.communication.subject,
        },
      });
    },
    getActivityLog() {
      this.loading = true;
      const params = {
        modelClass: this.modelClass,
        modelId: this.modelId,
      };
      axios
        .get(this.getUrl, { params })
        .then(({ data }) => {
          this.loading = false;
          this.convertItems(data);
        })
        .catch(console.error);
    },
    convertItems(data) {
      this.activities = data.data;
    },
  },
};
</script>
