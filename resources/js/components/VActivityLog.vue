<template>
  <div class="pt-lgSpace">
    <div class="activity activity--comment" v-if="allowComment">
      <div class="activity__user--avatar">
        <span>{{
          currentUser.charAt(0).toUpperCase() +
          currentUser.charAt(1).toUpperCase()
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
    <div class="flex items-center justify-end space-x-2 pt-4">
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
      </div>
      <slot />
    </div>
    <div
      v-show="loading"
      :aria-label="$t('activity-log.words.loading')"
      role="status"
      class="flex h-full items-center justify-center space-x-2 py-8"
    >
      <v-icon icon="ArrowPathIcon" class="h-lgSpace w-lgSpace animate-spin" />
      <span class="text-lg font-medium text-tertiary-500">{{
        $t("activity-log.words.loading")
      }}</span>
    </div>
    <div class="activity activity--min" v-for="activity in activities">
      <div class="activity__user activity__user--avatar">
        <span>{{
          activity.user.charAt(0).toUpperCase() +
          activity.user.charAt(1).toUpperCase()
        }}</span>
      </div>
      <div class="content">
        <div class="content__status">
          <div class="content__status--meta">
            <a href="#" class="font-medium text-gray-900">{{
              activity.user
            }}</a>
            <span v-html="activity.description"></span>
          </div>
          <div class="content__status--time">{{ activity.created_at }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import axios from "axios";

export default {
  name: "VActivityLog",
  components: {},
  inject: ["bus"],
  props: {
    getUrl: {
      type: String,
      default: "/api/generic/activity-logs",
    },
    commentUrl: {
      type: String,
      default: "api/generic/activity-logs/comments",
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
      filters: {
        "filter[term]": null,
      },
      searchKey: null,
      comment: null,
      activities: [],
    };
  },
  created() {
    this.bus.$on("activityLogTableFilterChange", ({ params }) => {
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
  },
};
</script>
