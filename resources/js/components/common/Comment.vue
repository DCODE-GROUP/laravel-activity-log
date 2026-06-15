<template>
  <div class="content relative">
    <!-- Overlay shown when loading -->
    <div
      v-if="loading"
      :aria-label="$t('activity-log.words.loading')"
      class="absolute inset-0 z-10 flex items-center justify-center bg-white bg-opacity-70"
    >
      <Icon class="h-lgSpace w-lgSpace animate-spin" icon="ArrowPathIcon" />
    </div>

    <div class="">
      <div class="content__text">
        <Mentionable
          v-if="canMentionInComment"
          :allowSpace="canMentionSpace"
          :items="items"
          :keys="['@']"
          filtering-disabled
          insert-space
          offset="10"
          @open="loadUsers()"
          @search="loadUsers($event)"
        >
          <div id="comment-input" :class="growWrap">
            <textarea
              v-model="comment"
              :placeholder="$t('activity-log.placeholders.add_comment')"
              class="content__text--textarea"
              rows="3"
              @keyup.enter="addCommentByEnter"
            ></textarea>
          </div>
          <template #no-result>
            <div class="dim">
              {{
                loading
                  ? $t("activity-log.fields.loading")
                  : $t("activity-log.fields.no_result")
              }}
            </div>
          </template>

          <template #item-@="{ item }">
            <div class="mention-wrapper">
              <div class="mention-wrapper--avatar activity__user--avatar">
                <span class="font-bold !text-sm">{{
                  item.label.charAt(0).toUpperCase() +
                  item.label.charAt(1).toUpperCase()
                }}</span>
              </div>
              <span class="dim">
                {{ item.label }}
              </span>
            </div>
          </template>
        </Mentionable>
        <div v-else>
          <div id="comment-input" :class="growWrap">
            <textarea
              v-model="comment"
              :placeholder="$t('activity-log.placeholders.add_comment')"
              class="content__text--textarea focus:ring-0"
              rows="3"
              @keyup.enter="addCommentByEnter"
            ></textarea>
          </div>
        </div>
      </div>

      <div class="content__action">
        <div class="content__action-attachment"></div>
        <div class="content__action-action flex">
          <div
            v-if="activity"
            class="content__action-button content__action-button--cancel cursor-pointer mr-smSpace"
            @click="cancel"
          >
            {{ $t("activity-log.buttons.cancel") }}
          </div>
          <div
            :class="{
              'content__action-button--disable':
                loading ||
                (activity ? activity.meta === comment || !comment : !comment),
            }"
            class="content__action-button cursor-pointer"
            @click="addComment"
          >
            {{
              activity
                ? $t("activity-log.buttons.save")
                : $t("activity-log.buttons.comment")
            }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { Mentionable } from "@dcodegroup/vue-mention";
import Icon from "./Icon.vue";

export default {
  name: "Comment",
  inject: ["bus"],
  components: {
    Mentionable,
    Icon,
  },
  props: {
    enterToComment: {
      type: Boolean,
      default: false,
    },
    commentUrl: {
      type: String,
      default: "/api/generic/activity-logs/comments",
    },
    loadUsersUrl: {
      type: String,
      default: "/api/generic/activity-logs/filters/facets/created_by",
    },
    modelClass: {
      type: String,
      required: true,
    },
    modelId: {
      type: String,
      required: true,
    },
    user: {
      type: String,
      required: false,
    },
    activity: {
      type: Object,
      required: false,
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
    autoExpandCommentInput: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      loading: false,
      comment: this.activity ? this.activity.meta : null,
      items: [],
    };
  },
  computed: {
    growWrap() {
      return this.autoExpandCommentInput ? "grow-wrap" : "";
    },
  },
  methods: {
    async loadUsers(searchText = null) {
      this.loading = true;
      axios
        .get(
          `${this.loadUsersUrl}?s=${searchText}&filter[admin]=1&modelClass=${this.modelClass}&modelId=${this.modelId}`,
        )
        .then((res) => {
          this.items = res.data.map((item) => {
            return {
              label: item.label,
              value: `[${item.label}]`,
              id: item.value,
            };
          });
          this.loading = false;
        });
    },
    addCommentByEnter() {
      if (this.enterToComment) {
        this.addComment();
      }
    },
    cancel() {
      this.$emit("cancelEditComment");
    },
    addComment() {
      if (!this.comment || this.loading) {
        return;
      }
      this.loading = true;
      const params = {
        modelClass: this.modelClass,
        modelId: this.modelId,
        comment: this.comment,
        currentUrl: window.location.href,
        currentUser: this.user,
        ...{ timezone: this.timezone },
      };
      if (this.activity) {
        axios
          .patch(this.commentUrl + "/" + this.activity.id, params)
          .then(({ data }) => {
            this.loading = false;
            this.$emit("cancelEditComment");
            this.$emit("addComment", data.data);
          })
          .catch(console.error);
      } else {
        axios
          .post(this.commentUrl, params)
          .then(({ data }) => {
            this.comment = null;
            this.loading = false;
            this.$emit("addComment", data.data);
          })
          .catch(console.error);
      }
    },
  },
};
</script>

<style scoped>
.grow-wrap {
  /* easy way to plop the elements on top of each other and have them both sized based on the tallest one's height */
  display: grid;
}

.grow-wrap::after {
  /* Note the weird space! Needed to preventy jumpy behavior */
  content: attr(data-replicated-value) " ";

  /* This is how textarea text behaves */
  white-space: pre-wrap;

  /* Hidden from view, clicks, and screen readers */
  visibility: hidden;
}

.grow-wrap > textarea {
  /* You could leave this, but after a user resizes, then it ruins the auto sizing */
  resize: none;

  /* Firefox shows scrollbar on growth, you can hide like this. */
  overflow: hidden;
}

.grow-wrap > textarea,
.grow-wrap::after {
  /* Identical styling required!! */
  border: 1px solid black;
  padding: 0.5rem;
  font: inherit;

  /* Place on top of each other */
  grid-area: 1 / 1 / 2 / 2;
}

.mention-wrapper {
  display: flex;
  padding: 6px;
  border-radius: 4px;
  gap: 8px;
  cursor: pointer;
  line-height: 20px;
  font-size: 14px;
}

.mention-wrapper--avatar {
  padding: 6px;
  margin-left: 10px;
}

.activity__user--avatar {
  @apply flex flex-shrink-0 h-xlSpace w-xlSpace cursor-pointer items-center justify-center rounded-full bg-gray-600 ring-0 ring-neutral-500 text-white;
}

.mention-selected .mention-wrapper {
  background: rgba(226, 232, 240, 1);
}

.mention-wrapper .number {
  font-family: monospace;
}

.dim {
  padding: 8px;
  color: #666;
}
</style>
