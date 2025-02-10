<template>
  <div class="w-full text-sm font-medium text-gray-900">
    <div class="flex items-center justify-between space-x-2">
      <div class="flex py-3 text-left font-semibold">
        {{ $t("activity-log.headings.title") }}
      </div>
      <div class="flex w-[21.875rem] space-x-1">
        <label class="relative block w-full">
          <input
            v-model="searchKey"
            :placeholder="$t('activity-log.search.placeholder')"
            class="pl-8"
            name="name"
            type="text"
            v-on:keyup.enter="searchTerm"
          />
          <button
            class="absolute left-2.5 top-1/2 -translate-y-1/2"
            type="button"
            @click="searchTerm"
          >
            <icon
              classes="text-primary-400 w-4 h-4"
              icon="MagnifyingGlassIcon"
            ></icon>
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
        <tr v-for="activity in activities" :key="activity.id">
          <td class="bg-slate-50 py-4 pl-3">
            {{
              activity.meta
                ? activity.meta.action
                : $t("activity-log.fields.updated_model")
            }}
          </td>
          <td class="whitespace-nowrap bg-slate-50 py-4">
            <div class="">
              <icon
                v-if="activity.communication"
                :icon="activity.communication.icon"
                classes="w-5 h-5 mr-xsSpace inline cursor-pointer hover:text-blue-400"
                @click="openModal(activity)"
              ></icon>
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
import Icon from "./common/Icon.vue";

export default {
  inject: ["bus"],
  components: { Icon },
  props: {
    getUrl: {
      type: String,
      default: "/activity-logs",
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
      default: "openActivityLogModal",
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
  beforeUnmount: function created() {
    this.bus.$off(this.filterEvent);
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
