<template>
  <div class="relative inline-flex items-center gap-2">
    <!-- Reaction counts -->
    <div class="flex items-center gap-1">
      <template v-for="emoji in emojis" :key="emoji + '_count'">
        <div v-if="(reactionGroups[emoji] || []).length > 0" class="relative">
          <button
              @mouseenter="hoveredReaction = emoji"
              @mouseleave="hoveredReaction = null"
              class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded bg-gray-100 text-gray-700 hover:bg-gray-200"
          >
            <span class="text-sm">{{ emoji }}</span>
            <span class="font-semibold">{{ (reactionGroups[emoji] || []).length }}</span>
          </button>
          <!-- Tooltip -->
          <div v-if="hoveredReaction === emoji"
               class="absolute top-full left-1/2 -translate-x-1/2 mt-2 flex flex-col gap-2 rounded bg-white px-3 py-2 shadow-xl ring-1 ring-gray-200 z-50 max-w-xs max-h-48 overflow-auto">
            <div v-for="r in (reactionGroups[emoji] || [])" :key="r.id" class="text-sm text-gray-700">
              <div class="flex items-center justify-between gap-2">
                <div class="truncate">{{ emoji }} {{ getReactionUserName(r.user) }}</div>
                <div v-if="r.user.created_at" class="text-xs text-gray-400">at {{ r.user.created_at  }}</div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Add reaction button -->
    <button
        class="inline-flex h-8 items-center gap-1 rounded-full bg-slate-200 px-3 text-slate-600 hover:bg-slate-300 transition grayscale"
        @mouseenter="enterPopover($event)"
        @mouseleave="leavePopover"
    >
      <span class="text-lg leading-none"><icon icon="FaceSmileIcon"></icon></span>
      <icon icon="PlusIcon" class="w-5 h-5"/>
    </button>

    <!-- Reaction picker popover -->
    <Transition
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="opacity-0 translate-y-2 scale-90"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0 translate-y-2 scale-90"
    >
      <div
          v-if="showPopover"
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
              @click="handleReact(emoji)"
              class="text-2xl px-2 py-1 transition duration-150 hover:-translate-y-1 hover:scale-110"
          >
            {{ emoji }}
          </button>
        </div>

        <!-- Following rows: users per emoji -->
        <!--        <div class="flex flex-col gap-1 max-h-40 overflow-auto">-->
        <!--          <div v-for="emoji in emojis" :key="emoji + '_users'" class="flex items-start gap-2">-->
        <!--            <div class="w-6">{{ emoji }}</div>-->
        <!--            <div class="flex flex-wrap gap-2">-->
        <!--              <span v-for="r in (reactionGroups[emoji] || [])" :key="r.id"-->
        <!--                    class="text-sm text-tertiary-500 px-2 py-1 rounded bg-gray-50">-->
        <!--                {{ getReactionUserName(r.user) }}-->
        <!--              </span>-->
        <!--              <span v-if="!(reactionGroups[emoji] || []).length"-->
        <!--                    class="text-sm text-tertiary-400">—</span>-->
        <!--            </div>-->
        <!--          </div>-->
        <!--        </div>-->

      </div>
    </Transition>
  </div>
</template>

<script>
import Icon from "./common/Icon.vue";

export default {
  name: 'VActivityReactions',
  components: {Icon},
  props: {
    activity: {
      type: Object,
      required: true,
    },
    currentUser: {
      type: Object,
      default: null,
    },
    getUrl: {
      type: String,
      required: true,
    },
    modelClass: {
      type: String,
      required: true,
    },
    modelId: {
      type: [String, Number],
      required: true,
    },
  },
  data() {
    return {
      emojis: ['😀', '👍', '👎', '👀', '✅'],
      showPopover: false,
      hidePopoverTimeout: null,
      hoveredReaction: null,
      loading: false,
    };
  },
  computed: {
    reactionGroups() {
      // prefer server-provided grouped data when available
      if (this.activity.reactionGroups) return this.activity.reactionGroups;
      if (this.activity.reaction_groups) return this.activity.reaction_groups;

      const groups = {};
      (this.activity.reactions || []).forEach((r) => {
        if (!groups[r.emoji]) groups[r.emoji] = [];
        groups[r.emoji].push(r);
      });
      return groups;
    },
  },
  methods: {
    enterPopover(event) {
      if (this.hidePopoverTimeout) {
        clearTimeout(this.hidePopoverTimeout);
        this.hidePopoverTimeout = null;
      }
      this.showPopover = true;
    },
    leavePopover() {
      if (this.hidePopoverTimeout) clearTimeout(this.hidePopoverTimeout);
      this.hidePopoverTimeout = setTimeout(() => {
        this.showPopover = false;
        this.hidePopoverTimeout = null;
      }, 150);
    },
    cancelHidePopover() {
      if (this.hidePopoverTimeout) {
        clearTimeout(this.hidePopoverTimeout);
        this.hidePopoverTimeout = null;
      }
    },
    getReactionUserName(user) {
      if (!user) return this.$t('activity-log.words.unknown') || 'Someone';
      return user.full_name || user.name || user.email || this.$t('activity-log.words.unknown') || 'Someone';
    },
    async handleReact(emoji) {
      this.$emit('react', emoji);
      this.showPopover = false;
    },
  },
};
</script>
