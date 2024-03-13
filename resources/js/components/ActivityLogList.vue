<template>
  <div class="w-full text-sm font-medium text-gray-900">
    <div class="flex items-center justify-between space-x-2">
      <div class="flex py-3 text-left font-semibold">
        {{ $t("activity-log-translations::headings.title") }}<br />
        {{ $t("activity-log::headings.title") }}<br />
        {{ $t("activity-log::activity-log.headings.title") }}<br />
        {{ $t("activity-log-translations::activity-log.headings.title") }}<br />
        {{ $t("activity-log::headings.title") }}<br />
        {{ $t("activity-log-translations::headings.title") }}<br />
        {{ $t("activity-log.headings.title") }}<br />
        {{ $t("vendor.dcodegroup.activity-log.headings.title") }} <br />
        {{ $t("dcodegroup.activity-log.en.activity-log.headings.title") }} <br />
        {{ $t("activity-log.en.activity-log.headings.title") }} <br />
        <br />
      </div>
      <div class="flex w-[21.875rem] space-x-1">
        <label class="relative block w-full">
          <input
            class="pl-8"
            type="text"
            name="name"
            v-model="searchKey"
            :placeholder="$t('activity_log.search.placeholder')"
            v-on:keyup.enter="searchTerm"
          />
          <button
            class="absolute left-2.5 top-1/2 -translate-y-1/2"
            type="button"
            @click="searchTerm"
          >
            <v-icon
              icon="MagnifyingGlassIcon"
              classes="text-primary-400 w-4 h-4"
            ></v-icon>
          </button>
        </label>
        <slot />
      </div>
    </div>

    <table class="min-w-full border-separate border-spacing-y-2">
      <colgroup>
        <col class="w-2/12" />
        <col class="w-6/12" />
        <col class="w-2/12" />
        <col class="w-2/12" />
      </colgroup>
      <tbody class="">
        <tr :key="activity.id" v-for="activity in activities">
          <td class="bg-slate-50 py-4 pl-3">
            {{
              activity.meta
                ? activity.meta.action
                : $t("activity-log.fields.tender_updated")
            }}
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
            {{ activity?.meta?.created_by_label || $t("generic.created_by") }}:
            {{ activity?.meta?.created_by || activity.user }}
          </td>
          <td class="bg-slate-50 px-3 py-4 text-right">
            {{ activity.created_at_date }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<script>
import axios from "axios";
import VIcon from "./VIcon.vue";

export default {
  inject: ["bus"],
  components: { VIcon },
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
      default: "openModal",
    },
    filterEvent: {
      type: String,
      default: "activityLogFilterChange",
    },
  },
  data() {
    return {
      loading: false,
      comment: null,
      activities: [],
      filters: {
        "filter[term]": null,
      },
    };
  },
  created() {
    this.bus.$on(this.filterEvent, ({ params }) => {
      this.filters = Object.assign({}, params, {
        "filter[term]": this.filters["filter[term]"],
      });
      this.$nextTick(() => this.getActivityLog());
    });
  },
  mounted() {
    this.getActivityLog();
  },
  methods: {
    searchTerm() {
      this.filters[`filter[term]`] = this.searchKey;
      this.$nextTick(() => this.getActivityLog());
    },
    openModal(activity) {
      this.bus.$emit(this.modalEvent, {
        componentName: "SummaryEmail",
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
        ...this.filters,
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
