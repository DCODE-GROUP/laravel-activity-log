<template>
  <div class="">
    <div
      class="readmore-container"
      :style="enable && !isOpen ? { webkitLineClamp: lines } : {}"
    >
      <span v-html="content"></span>
      <span v-if="isEdited" class="readmore-container--edited">
        ({{ $t("activity-log.words.edited") }})</span
      >
    </div>
    <template v-if="this.enable">
      <a @click.prevent="toggle" class="inline text-blue-600 cursor-pointer">{{
        isOpen
          ? $t("activity-log.words.read_less")
          : $t("activity-log.words.read_more")
      }}</a>
    </template>
  </div>
</template>

<script>
export default {
  name: "ReadMoreContent",
  props: {
    content: {
      type: String,
      required: true,
    },
    lines: {
      type: Number,
      default: 1,
    },
    enable: {
      type: Boolean,
      default: true,
    },
    open: {
      type: Boolean,
      default: false,
    },
    isEdited: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      isOpen: this.open,
    };
  },
  methods: {
    toggle() {
      this.isOpen = !this.isOpen;
    },
  },
};
</script>

<style scoped>
.readmore-container {
  overflow: hidden;
  position: relative;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  white-space: pre-wrap;
}
.readmore-container--edited {
  font-size: 0.75rem; /* 12px */
  line-height: 1rem; /* 16px */
  color: rgb(148 163 184);
}
</style>
