<template>
  <div class="pt-lgSpace w-full">
    <div class="activity activity--comment" v-if="allowComment">
      <div class="activity__user--avatar !w-[48px] !h-[48px]">
        <span class="font-bold !text-lg">{{
          username.charAt(0).toUpperCase() + username.charAt(1).toUpperCase()
        }}</span>
      </div>
      <div class="content">
        <div class="content__text">
          <textarea
            @keyup.enter="addComment"
            class="content__text--textarea focus:ring-0"
            v-model="comment"
            rows="3"
            :placeholder="$t('activity-log.placeholders.add_comment')"
          ></textarea>
        </div>

        <div class="content__action">
          <div class="content__action-attachment"></div>
          <div
            class="content__action-button cursor-pointer"
            :class="{ 'content__action-button--disable': !comment }"
            @click="addComment"
          >
            {{ $t("activity-log.buttons.save_comment") }}
          </div>
        </div>
      </div>
    </div>
    <div class="flex items-end justify-between space-x-2 py-smSpace pl-xlSpace">
      <div class="flex justify-start">
        <toggle
          :value="isCollapsedView"
          :title="$t('activity-log.fields.collapsed_view')"
          @input="collapView($event)"
          class="pr-smSpace"
        ></toggle>
        <toggle
          :value="isFilterUser"
          :title="$t('activity-log.fields.my_activities')"
          @input="filterUser($event)"
        ></toggle>
      </div>
      <div class="flex justify-end w-[21.875rem] space-x-1">
        <label class="relative block w-full">
          <input
            class="pl-8"
            type="text"
            name="name"
            v-model="searchKey"
            :placeholder="$t('activity-log.placeholders.search_description')"
            v-on:keyup.enter="searchTerm"
          />
          <button
            class="absolute left-2.5 top-1/2 -translate-y-1/2"
            type="button"
            @click="searchTerm"
          >
            <icon
              icon="MagnifyingGlassIcon"
              classes="text-primary-400 w-4 h-4"
            ></icon>
          </button>
        </label>
        <slot />
      </div>
    </div>
    <div
      v-show="loading"
      :aria-label="$t('activity-log.words.loading')"
      role="status"
      class="flex h-full items-center justify-center space-x-2 py-8"
    >
      <icon icon="ArrowPathIcon" class="h-lgSpace w-lgSpace animate-spin" />
      <span class="text-lg font-medium text-tertiary-500">{{
        $t("activity-log.words.loading")
      }}</span>
    </div>
    <div
      class="activity activity--min relative !mt-0 pt-3"
      v-for="(activity, index) in activities"
    >
      <div class="absolute left-[31px] h-full w-[1px] bg-slate-200"></div>
      <div
        class="flex justify-center items-center relative rounded-xl w-[48px] h-[48px] cursor-pointer"
        :class="'bg-' + activity.color + '-50'"
      >
        <icon
          v-if="activity.type"
          :classes="'w-[24px] h-[24px] text-' + activity.color + '-500'"
          :icon="activity.icon"
        ></icon>
        <span
          class="absolute -right-1 -bottom-1 justify-center text-[10px] font-bold tracking-widest w-[24px] h-[24px] flex justify-center items-center text-white rounded-full bg-gray-600 ring-0 ring-neutral-500"
          >{{
            username.charAt(0).toUpperCase() + username.charAt(1).toUpperCase()
          }}</span
        >
      </div>
      <div class="content">
        <div class="content__status">
          <div class="content__status--meta">
            <a href="#" class="font-medium text-gray-900">{{ activity.user }}</a
            >&nbsp
            <span v-html="activity.title"></span>
            <br />
            <div v-if="!collapseStage[index]" class="pt-smSpace">
              <button
                v-if="activity.communication"
                class="btn btn--secondary flex-row-reverse space-x-reverse"
                type="button"
                @click="openModal(activity)"
              >
              {{  $t("activity-log.buttons.preview_email") }}
                <div class="btn-icon btn__icon--left">
                  <icon icon="EnvelopeIcon"></icon>
                </div>
              </button>
              <span v-else v-html="activity.description"></span>
            </div>
          </div>
          <div class="content__status--time flex">
            {{ activity.created_at_date }}
            <a
              class="cursor-pointer px-smSpace items-center"
              @click.prevent="collapseStage[index] = !collapseStage[index]"
            >
              <icon
                v-if="collapseStage[index]"
                icon="ChevronUpIcon"
                classes="text-primary-400 w-4 h-4"
              ></icon>
              <icon
                v-else
                icon="ChevronDownIcon"
                classes="text-primary-400 w-4 h-4"
              ></icon>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import Icon from "./common/Icon.vue";
import Toggle from "./common/Toggle.vue";

export default {
  inject: ["bus"],
  components: { Icon, Toggle },
  props: {
    getUrl: {
      type: String,
      default: "/api/generic/activity-logs",
    },
    commentUrl: {
      type: String,
      default: "/api/generic/activity-logs/comments",
    },
    modelClass: {
      type: String,
      required: true,
    },
    modelId: {
      type: String,
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
      type: Object,
      required: false,
    },
    filterEvent: {
      type: String,
      default: "activityLogFilterChange",
    },
    modalEvent: {
      type: String,
      default: "openModal",
    },
  },
  data() {
    return {
      username:
        this.currentUser.full_name ?? this.$t("activity-log.fields.system"),
      collapseStage: {},
      isCollapsedView: false,
      isFilterUser: false,
      loading: false,
      filters: {
        "filter[term]": null,
      },
      searchKey: null,
      comment: null,
      activities: [],
      colors: [
        "bg-violet-50",
        "text-violet-500",
        "bg-teal-50",
        "text-teal-500",
        "bg-orange-50",
        "text-orange-500",
        "bg-pink-50",
        "text-pink-500",
      ],
    };
  },
  created() {
    this.bus.$on(this.filterEvent, ({ params }) => {
      this.filters = Object.assign({}, params, {
        "filter[term]": this.filters["filter[term]"],
      });
      this.$nextTick(() => this.getActivityLog());
    });

    this.bus.$on("activityLogTermChanged", ({ term, name }) => {
      this.filters[`filter[${name}]`] = term;
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

    getActivityLog() {
      this.loading = true;
      this.activities = [];
      const params = {
        modelClass: this.modelClass,
        modelId: this.modelId,
        ...this.filters,
      };
      axios
        .get(this.getUrl, { params })
        .then(({ data }) => {
          this.loading = false;
          this.activities = [];
          if (data.data.length) {
            this.activities = data.data;
          }
        })
        .catch(console.error);
    },

    addComment() {
      if (!this.comment) {
        return;
      }
      axios
        .post(this.commentUrl, {
          modelClass: this.modelClass,
          modelId: this.modelId,
          comment: this.comment,
        })
        .then(({ data }) => {
          this.activities = [];
          this.comment = null;
          if (data.data.length) {
            this.activities = data.data;
          }

          if (this.refreshSelf) {
            this.getActivityLog();
          }
        })
        .catch(console.error);
    },
    collapView($event) {
      if ($event) {
        this.collapseStage = this.activities.map((value, index) => {
          return { index: true };
        });
      } else {
        this.collapseStage = {};
      }
      this.isCollapsedView = $event;
    },
    filterUser($event) {
      if ($event && this.currentUser && this.currentUser.id) {
        this.filters[`filter[created_by]`] = this.currentUser.id;
      } else {
        this.filters[`filter[created_by]`] = "";
      }
      this.isFilterUser = $event;
      this.bus.$emit(this.filterEvent, {
        params: this.filters,
        field: "created_by",
      });
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
  },
};
</script>
