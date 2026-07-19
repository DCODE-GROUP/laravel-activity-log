<template>
  <div class="pt-lgSpace w-full">
    <div v-if="allowComment" class="activity activity--comment">
      <div class="activity__user--avatar !w-[48px] !h-[48px]">
        <span class="font-bold !text-lg">{{
            username.charAt(0).toUpperCase() +
            getUserKeyName(username).toUpperCase()
          }}</span>
      </div>
      <comment
          :auto-grow-input="autoGrowInput"
          :can-mention-in-comment="canMentionInComment"
          :can-mention-space="canMentionSpace"
          :comment-url="commentUrl"
          :enter-to-comment="enterToComment"
          :load-users-url="loadUsersUrl"
          :model-class="modelClass"
          :model-id="modelId"
          :timezone="timezone"
          :user="username"
          @addComment="addComment($event)"
      ></comment>
    </div>
    <div
        v-if="!isWidgetView"
        class="flex items-end justify-between space-x-2 py-smSpace"
    >
      <div class="flex justify-start space-x-2">
        <div class="w-[48px]"></div>
        <toggle
            :title="$t('activity-log.fields.collapsed_view')"
            :value="isCollapsedView"
            class="pr-smSpace"
            @input="collapView($event)"
        ></toggle>
        <toggle
            :title="$t('activity-log.fields.my_activities')"
            :value="isFilterUser"
            @input="filterUser($event)"
        ></toggle>
      </div>
      <div class="flex justify-end w-[21.875rem] space-x-1">
        <label class="relative block w-9/12">
          <input
              v-model="searchKey"
              :placeholder="$t('activity-log.placeholders.search_description')"
              class="pl-8"
              name="name"
              type="text"
              v-on:keydown.enter.stop.prevent="searchTerm"
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
        <slot class="w-3/12"/>
      </div>
    </div>
    <div
        v-if="loading"
        :aria-label="$t('activity-log.words.loading')"
        class="flex h-full items-center justify-center space-x-2 py-8"
        role="status"
    >
      <icon class="h-lgSpace w-lgSpace animate-spin" icon="ArrowPathIcon"/>
      <span class="text-lg font-medium text-tertiary-500">{{
          $t("activity-log.words.loading")
        }}</span>
    </div>
    <div v-if="!loading">
      <template v-if="activities.length">
        <div
            v-for="(activity, index) in activities"
            :class="{ 'pt-8': !isWidgetView, 'pb-lgSpace': isWidgetView }"
            class="activity activity--min relative !mt-0 pl-0"
        >
          <div
              v-show="index < activities.length - 1"
              class="absolute left-[24px] h-full w-[1px] bg-slate-200"
          ></div>
          <div
              :class="'bg-' + activity.color + '-50'"
              class="flex justify-center items-center relative rounded-xl min-w-[48px] w-[48px] h-[48px] cursor-pointer"
          >
            <icon
                v-if="activity.type"
                :classes="'w-[18px] h-[18px] text-' + activity.color + '-500'"
                :icon="activity.icon"
            ></icon>
            <span
                class="absolute -right-1 -bottom-1 justify-center text-[10px] font-bold tracking-widest w-[24px] h-[24px] flex justify-center items-center text-white rounded-full bg-gray-600 ring-0 ring-neutral-500"
            >{{
                activity.user.charAt(0).toUpperCase() +
                getUserKeyName(activity.user).toUpperCase()
              }}</span
            >
          </div>
          <div :id="'activity_' + activity.id" class="content">
            <div v-if="activity.id !== editId" class="content__status min-h-[120px]">
              <div class="content__status--meta">
                <a class="font-medium text-gray-900" href="#">{{
                    activity.user
                  }}</a
                >&nbsp
                <span v-html="activity.title"></span>
                <div
                    class="relative inline-block"
                    @mouseenter="enterPopover(activity, $event)"
                    @mouseleave="leavePopover"
                >
                  <button
                      class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition"
                  >
                   <span
                       :class="userReaction(activity) ? '' : 'grayscale opacity-60'"
                       class="text-xl"
                   >{{'👍' }}</span>
                  </button>

                  <Transition
                      enter-active-class="transition duration-150 ease-out"
                      enter-from-class="opacity-0 translate-y-2 scale-90"
                      enter-to-class="opacity-100 translate-y-0 scale-100"
                      leave-active-class="transition duration-100 ease-in"
                      leave-from-class="opacity-100"
                      leave-to-class="opacity-0 translate-y-2 scale-90"
                  >
                    <div
                        v-if="showPopoverFor === activity.id"
                        @mouseenter="cancelHidePopover"
                        @mouseleave="leavePopover"
                        class="absolute top-full left-1/2 -translate-x-1/2 mt-2 flex flex-col gap-2 rounded bg-white px-3 py-2 shadow-xl ring-1 ring-gray-200"
                        style="z-index: 99999;"
                    >
                      <!-- First row: emojis -->
                      <div class="flex items-center gap-2">
                        <button
                          v-for="emoji in emojis"
                          :key="emoji"
                          @click="react(emoji, activity)"
                          class="text-2xl px-2 py-1 transition duration-150 hover:-translate-y-1 hover:scale-110"
                        >
                          {{ emoji }}
                        </button>
                      </div>

                      <!-- Following rows: users per emoji -->
                      <div class="flex flex-col gap-1 max-h-40 overflow-auto">
                        <div v-for="emoji in emojis" :key="emoji + '_users'" class="flex items-start gap-2">
                          <div class="w-6">{{ emoji }}</div>
                          <div class="flex flex-wrap gap-2">
                            <span v-for="r in (reactionGroups(activity)[emoji] || [])" :key="r.id" class="text-sm text-tertiary-500 px-2 py-1 rounded bg-gray-50">
                              {{ getReactionUserName(r.user) }}
                            </span>
                            <span v-if="!(reactionGroups(activity)[emoji] || []).length" class="text-sm text-tertiary-400">—</span>
                          </div>
                        </div>
                      </div>

                    </div>
                  </Transition>

<!--                  <div class="mt-2 flex items-center gap-2">-->
<!--                    <button-->
<!--                      v-for="(group, emoji) in reactionGroups(activity)"-->
<!--                      :key="emoji"-->
<!--                      @click.prevent="react(emoji, activity)"-->
<!--                      class="flex items-center space-x-1 text-sm px-2 py-1 rounded-full bg-gray-100"-->
<!--                    >-->
<!--                      <span :class="{'text-primary-500': group.some(r => r.user && currentUser && r.user.id === currentUser.id)}">{{ emoji }}</span>-->
<!--                      <span class="text-xs text-tertiary-500">{{ group.length }}</span>-->
<!--                    </button>-->
<!--                  </div>-->
                </div>
                <br/>
                <div v-if="!collapseStage[index]" class="pt-smSpace">
                  <div
                      v-if="activity.communication"
                      class="flex items-center space-x-2 sm:flex-col sm:space-x-0 sm:space-y-smSpace sm:items-start"
                  >
                    <div class="flex gap-2">
                      <button
                          class="btn btn--secondary max-h-[32px] rounded-lg"
                          type="button"
                          @click="openModal(activity)"
                      >
                        <div
                            v-if="activity.communication.type === 'Email'"
                            class="flex items-center flex-row-reverse space-x-reverse"
                        >
                          <span>{{
                              $t("activity-log.buttons.preview_email")
                            }}</span>
                          <div class="btn-icon btn__icon--left">
                            <icon icon="EnvelopeIcon"></icon>
                          </div>
                        </div>

                        <div
                            v-if="activity.communication.type === 'Sms'"
                            class="flex items-center flex-row-reverse space-x-reverse"
                        >
                          <span>{{
                              $t("activity-log.buttons.preview_sms")
                            }}</span>
                          <div class="btn-icon btn__icon--left">
                            <icon icon="ChatBubbleLeftRightIcon"></icon>
                          </div>
                        </div>
                      </button>
                      <button
                          v-if="allowResend"
                          :disabled="resent"
                          class="btn btn--secondary max-h-[32px] rounded-lg"
                          type="button"
                          @click="resentCommunication(activity.communication)"
                      >
                        <div
                            class="flex items-center flex-row-reverse space-x-reverse"
                        >
                          <span>{{
                              resent
                                  ? $t("activity-log.buttons.resent")
                                  : $t("activity-log.buttons.resend")
                            }}</span>
                          <div class="btn-icon btn__icon--left">
                            <icon icon="ArrowPathIcon"></icon>
                          </div>
                        </div>
                      </button>
                    </div>
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
                  <div
                      v-else-if="activity.description"
                      class="content__status--description"
                  >
                    <read-more-content
                        :content="activity.description"
                        :is-edited="activity.is_edited"
                        :show-full-comment="showFullComment"
                    ></read-more-content>
                  </div>
                  <div
                      v-if="activity.type === 'Phone Call'"
                      class="flex items-center space-x-2 sm:flex-col sm:space-x-0 sm:space-y-smSpace sm:items-start py-smSpace"
                  >
                    <a
                        :href="activity.meta"
                        class="btn btn--secondary max-h-[32px] rounded-lg"
                    >
                      <div
                          class="flex items-center flex-row-reverse space-x-reverse"
                      >
                        <span>{{
                            $t("activity-log.buttons.download_phone_call")
                          }}</span>
                        <div class="btn-icon btn__icon--left">
                          <icon icon="ArrowDownTrayIcon"></icon>
                        </div>
                      </div>
                    </a>
                  </div>
                </div>
              </div>
              <div class="content__status--time block">
                <div class="flex">
                  <span
                      :class="{ 'pr-7': !activity.description }"
                      class="pr-smSpace"
                  >
                    {{ activity.created_at_date }}
                  </span>
                  <a
                      v-if="activity.description"
                      class="cursor-pointer pr-3xsSpace items-center"
                      @click.prevent="individualCollapse(index)"
                  >
                    <icon
                        v-if="collapseStage[index]"
                        classes="text-primary-400 w-4 h-4"
                        icon="ChevronUpIcon"
                    ></icon>
                    <icon
                        v-else
                        classes="text-primary-400 w-4 h-4"
                        icon="ChevronDownIcon"
                    ></icon>
                  </a>
                </div>
                <div v-if="activity.type === 'Comment' && !isWidgetView">
                  <action
                      :activity="activity"
                      :get-url="getUrl"
                      :modal-event="modalEvent"
                      @addComment="addComment($event)"
                      @editComment="editComment($event)"
                  ></action>
                </div>
              </div>
            </div>
            <template v-else>
              <comment
                  :activity="activity"
                  :auto-grow-input="autoGrowInput"
                  :comment-url="commentUrl"
                  :load-users-url="loadUsersUrl"
                  :model-class="modelClass"
                  :model-id="modelId"
                  :timezone="timezone"
                  :user="username"
                  @addComment="addComment($event)"
                  @cancelEditComment="editId = null"
              ></comment>
            </template>
          </div>
        </div>
      </template>
      <div
          v-else
          class="flex h-full items-center justify-center space-x-2 py-8"
          role="status"
      >
        <span class="text-lg font-medium text-tertiary-500">{{
            noActivityText
          }}</span>
      </div>
    </div>
    <activity-log-modal></activity-log-modal>
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
  components: {ReadMoreContent, Icon, Toggle, Comment, Action},
  props: {
    getUrl: {
      type: String,
      default: "/activity-logs",
    },
    commentUrl: {
      type: String,
      default: "/activity-logs/comments",
    },
    loadUsersUrl: {
      type: String,
      default: "/activity-logs/filters/facets/created_by",
    },
    resendUrl: {
      type: String,
      default: "/activity-logs/resent-communication",
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
    allowResend: {
      type: Boolean,
      default: false,
    },
    isWidgetView: {
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
    resendEvent: {
      type: String,
      default: "activityLogResend",
    },
    modalEvent: {
      type: String,
      default: "openActivityLogModal",
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
    timezone: {
      type: String,
      required: false,
    },
    canMentionInComment: {
      type: Boolean,
      default: true,
    },
    canMentionSpace: {
      type: Boolean,
      default: true,
    },
    noActivityText: {
      type: String,
      default: "No activity found",
    },
    extra_models: {
      type: String,
    },
    showFullComment: {
      type: Boolean,
      default: false,
    },
    autoGrowInput: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
    showPopoverFor: null,
      hidePopoverTimeout: null,
      popoverPosition: null,
      emojis: ['👍', '👎', '👀', '✅'],
      selectedReaction: null,
      username: this.currentUser
          ? this.currentUser.full_name
          : this.$t("activity-log.fields.system"),
      collapseStage: {},

      isCollapsedView: this.defaultCollapView,
      isFilterUser: false,
      loading: false,
      filters: {
        "filter[term]": null,
      },
      searchKey: null,
      activities: [],
      editId: null,
      resent: null,
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
    this.bus.$on("refreshActivityLog", (payload) => {
      // Back-compat: if no payload, refresh everyone (old callers)
      if (!payload) {
        this.$nextTick(() => this.getActivityLog());
        return;
      }

      const myKey = `${this.modelClass}:${this.modelId}:${this.extra_models || ""}`;

      // Only refresh if the message matches THIS instance
      if (payload.key === myKey) {
        // OPTIONAL: skip if the sender is this component
        if (payload.senderUid && payload.senderUid === this._uid) return;

        this.$nextTick(() => this.getActivityLog());
      }
    });

    this.bus.$on(this.filterEvent, ({params}) => {
      this.filters = Object.assign({}, params, {
        "filter[term]": this.filters["filter[term]"],
      });
      this.$nextTick(() => this.getActivityLog());
    });

    this.bus.$on("activityLogTermChanged", ({term, name}) => {
      this.filters[`filter[${name}]`] = term;
      this.$nextTick(() => this.getActivityLog());
    });

    this.bus.$on("refreshActivityLog", () => {
      this.$nextTick(() => this.getActivityLog());
    });
  },

  async mounted() {
    await this.getActivityLog();
    this.collapView(this.defaultCollapView);
  },

  beforeUnmount: function created() {
    this.bus.$off(this.filterEvent);
    this.bus.$off("activityLogTermChanged");
  },

  computed: {
    popoverStyle() {
      if (!this.popoverPosition) return {};
      return {
        position: 'absolute',
        top: `${this.popoverPosition.top}px`,
        left: `${this.popoverPosition.left}px`,
        transform: 'translateX(-50%)',
        zIndex: 9999,
      };
    },
  },

  methods: {
    getUserKeyName(username) {
      const spaceIndex = username.indexOf(" ");
      return spaceIndex > -1
          ? username.charAt(spaceIndex + 1)
          : username.charAt(1);
    },
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
        ...{timezone: this.timezone},
        ...{extra_models: this.extra_models},
        ...this.filters,
      };
      return axios
          .get(this.getUrl, {params})
          .then(({data}) => {
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
          date: activity.communication.date,
          isMarkdownContent: this.isMarkdownContent,
        },
      });
    },
    resentCommunication(communication) {
      this.loading = true;
      axios
          .post(`${this.resendUrl}/${communication.id}`)
          .then(() => {
            this.loading = false;
            this.resent = true;
          })
          .catch(console.error)
          .finally(() => {
            this.loading = false;
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

      this.$emit("commentAdded", {
        event: $event,
        activities: this.activities,
        modelId: this.modelId,
        modelClass: this.modelClass,
      });

      this.bus.$emit("refreshActivityLog", {
        key: `${this.modelClass}:${this.modelId}:${this.extra_models || ""}`,
        senderUid: this._uid, // Vue internal uid; good enough for “don’t refresh self”
      });
    },
    editComment($event) {
      this.editId = $event;
    },
    enterPopover(activity, event) {
      if (this.hidePopoverTimeout) {
        clearTimeout(this.hidePopoverTimeout);
        this.hidePopoverTimeout = null;
      }

      // older browsers or missing event: ignore positioning and show inline popover
      this.popoverPosition = null;
      this.showPopoverFor = activity.id;
    },
    leavePopover() {
      if (this.hidePopoverTimeout) clearTimeout(this.hidePopoverTimeout);
      this.hidePopoverTimeout = setTimeout(() => {
        this.showPopoverFor = null;
        this.hidePopoverTimeout = null;
      }, 150);
    },
    cancelHidePopover() {
      if (this.hidePopoverTimeout) {
        clearTimeout(this.hidePopoverTimeout);
        this.hidePopoverTimeout = null;
      }
    },
    reactionGroups(activity) {
      // prefer server-provided grouped data when available
      if (activity.reactionGroups) return activity.reactionGroups;
      if (activity.reaction_groups) return activity.reaction_groups;

      const groups = {};
      (activity.reactions || []).forEach((r) => {
        if (!groups[r.emoji]) groups[r.emoji] = [];
        groups[r.emoji].push(r);
      });
      return groups;
    },


    userReaction(activity) {
      if (!this.currentUser) return null;
      const found = (activity.reactions || []).find((r) => r.user && this.currentUser && r.user.id === (this.currentUser.id));
      return found ? found.emoji : null;
    },
    getReactionUserName(user) {
      if (!user) return this.$t('activity-log.words.unknown') || 'Someone';
      return user.full_name || user.name || user.email || this.$t('activity-log.words.unknown') || 'Someone';
    },
    async react(emoji, activity) {
      this.selectedReaction = emoji;
      this.showPopoverFor = null;

      if (!activity || !activity.id) return;

      try {
        this.loading = true;
        const payload = { emoji, modelClass: this.modelClass, modelId: this.modelId };
        if (this.currentUser) payload.currentUser = this.currentUser;
        const { data } = await axios.post(`${this.getUrl}/${activity.id}/reactions`, payload);
        this.loading = false;
        this.activities = [];
        if (data && data.data && data.data.length) {
          this.activities = data.data;
        }
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    }
  },
};
</script>
