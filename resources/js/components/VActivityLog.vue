<template>
  <div class="pt-lgSpace w-full">
    <div class="activity activity--comment" v-if="allowComment">
      <div class="activity__user--avatar !w-[48px] !h-[48px]">
        <span class="font-bold !text-lg">{{
          username.charAt(0).toUpperCase() + username.charAt(1).toUpperCase()
        }}</span>
      </div>
      <comment
        :enter-to-comment="enterToComment"
        :model-class="modelClass"
        :model-id="modelId"
        :comment-url="commentUrl"
        :user="username"
        @addComment="addComment($event)"
      ></comment>
    </div>
    <div class="flex items-end justify-between space-x-2 py-smSpace">
      <div class="flex justify-start space-x-2">
        <div class="w-[48px]"></div>
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
        <label class="relative block w-9/12">
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
        <slot class="w-3/12" />
      </div>
    </div>
    <div
      v-if="loading"
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
      class="activity activity--min relative !mt-0 pt-8 pl-0"
      v-for="(activity, index) in activities"
    >
      <div
        v-show="index < activities.length - 1"
        class="absolute left-[24px] h-full w-[1px] bg-slate-200"
      ></div>
      <div
        class="flex justify-center items-center relative rounded-xl min-w-[48px] w-[48px] h-[48px] cursor-pointer"
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
      <div class="content" :id="'activity_' + activity.id">
        <div class="content__status">
          <div class="content__status--meta">
            <a href="#" class="font-medium text-gray-900">{{ activity.user }}</a
            >&nbsp
            <span v-html="activity.title"></span>
            <br />
            <div v-if="!collapseStage[index]" class="pt-smSpace">
              <div
                v-if="activity.communication"
                class="flex items-center space-x-2 sm:flex-col sm:space-x-0 sm:space-y-smSpace sm:items-start"
              >
                <button
                  class="btn btn--secondary max-h-[32px] rounded-lg"
                  type="button"
                  @click="openModal(activity)"
                >
                  <div
                    class="flex items-center flex-row-reverse space-x-reverse"
                    v-if="activity.communication.type === 'Email'"
                  >
                    <span>{{ $t("activity-log.buttons.preview_email") }}</span>
                    <div class="btn-icon btn__icon--left">
                      <icon icon="EnvelopeIcon"></icon>
                    </div>
                  </div>

                  <div
                    class="flex items-center flex-row-reverse space-x-reverse"
                    v-if="activity.communication.type === 'Sms'"
                  >
                    <span>{{ $t("activity-log.buttons.preview_sms") }}</span>
                    <div class="btn-icon btn__icon--left">
                      <icon icon="ChatBubbleLeftRightIcon"></icon>
                    </div>
                  </div>
                </button>
                <div v-if="activity.communication.type === 'Email'">
                  <span v-if="activity.communication.reads_count"
                    >{{ $t("activity-log.phases.opened_on") }}
                    {{ activity.communication.read_at_date }} ({{
                      activity.communication.reads_count
                    }}
                    {{ $t("activity-log.words.views") }})</span
                  >
                  <span v-else>{{
                    $t("activity-log.phases.email_has_not_been_opened")
                  }}</span>
                </div>
              </div>
              <div v-else class="content__status--description">
                <read-more-content
                  v-if="activity.description"
                  :content="activity.description"
                  :lines="2"
                ></read-more-content>
              </div>
            </div>
          </div>
          <div class="content__status--time block">
            <div class="flex">
              {{ activity.created_at_date }}
              <a
                class="cursor-pointer px-smSpace items-center"
                @click.prevent="individualCollapse(index)"
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
            <div v-if="activity.type === 'Comment'">
              <action
                :modal-event="modalEvent"
                :activity="activity"
                :get-url="getUrl"
              ></action>
            </div>
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
import Comment from "./common/Comment.vue";
import Action from "./common/Action.vue";
import ReadMoreContent from "./common/ReadMoreContent.vue";

export default {
  inject: ["bus"],
  components: { ReadMoreContent, Icon, Toggle, Comment, Action },
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
    activityEmailComponentName: {
      type: String,
      default: "ActivityEmail",
    },
    enterToComment: {
      type: Boolean,
      default: false,
    },
    isMarkdownContent: {
      type: Boolean,
      default: false,
    },
    defaultCollapView: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      username:
        this.currentUser.full_name ?? this.$t("activity-log.fields.system"),
      collapseStage: {},
      isCollapsedView: this.defaultCollapView,
      isFilterUser: false,
      loading: false,
      filters: {
        "filter[term]": null,
      },
      searchKey: null,
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
  async mounted() {
    await this.getActivityLog();
    this.collapView(this.defaultCollapView);
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
      return axios
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

    collapView($event) {
      if ($event) {
        const newCollapseStage = {};
        this.activities.reduce((current, next, index) => {
          current[index] = true;
          return current;
        }, newCollapseStage);
        this.collapseStage = newCollapseStage;
      } else {
        this.collapseStage = {};
      }
      this.isCollapsedView = $event;
    },
    individualCollapse(index) {
      const newValue = !this.collapseStage[index];
      this.collapseStage[index] = newValue;

      if (!newValue) {
        this.isCollapsedView = false;
        return;
      }

      this.$nextTick(() => {
        const allIsCollapsedView = Object.values(this.collapseStage).every(
          (isCollapsed) => isCollapsed,
        );

        if (!allIsCollapsedView) {
          return;
        }

        this.collapView(true);
      });
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
    },
    openModal(activity) {
      this.bus.$emit(this.modalEvent, {
        componentName: this.activityEmailComponentName,
        componentData: {
          content: activity.communication.content,
          to: activity.communication.to,
          subject: activity.communication.subject,
          isMarkdownContent: this.isMarkdownContent,
        },
      });
    },
    addComment($event) {
      this.activities = [];
      if ($event.length) {
        this.activities = $event;
      }

      if (this.refreshSelf) {
        this.getActivityLog();
      }
    },
  },
};
</script>
