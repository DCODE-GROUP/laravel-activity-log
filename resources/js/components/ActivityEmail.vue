<template>
  <div class="w-[600px] px-smSpace">
    <div class="border-b pb-mdSpace pt-smSpace">
      <div>
        {{ $t("activity-log.fields.to") }}
        <span class="font-semibold">{{ to }}</span>
      </div>
      <div>
        {{ $t("activity-log.fields.subject") }}
        <span class="font-semibold">{{ subject }}</span>
      </div>
      <div>
        {{ $t("activity-log.fields.date") }}
        <span class="font-semibold">{{ date }}</span>
      </div>
    </div>
    <div class="pt-smSpace" v-if="showMarkdownContent">
      <vue-markdown :source="content" />
    </div>
    <template v-else>
      <div class="pt-smSpace" v-html="content"></div>
    </template>
  </div>
</template>
<script>
import VueMarkdown from "vue-markdown-render";
export default {
  name: "ActivityEmail",
  components: {
    VueMarkdown,
  },
  props: {
    to: {
      type: String,
      required: true,
    },
    subject: {
      type: String,
      required: true,
    },
    content: {
      type: String,
      required: true,
    },
    date: {
      type: String,
      required: false,
    },
    isMarkdownContent: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    showMarkdownContent() {
      const markdownPatterns = [
        /^#{1,6} /m,            // # Heading
        /\*\*(.*?)\*\*/g,       // bold
        /\*(.*?)\*/g,           // italic
        /\[(.*?)\]\((.*?)\)/g,  // link
        /^>\s/m,                // blockquote
        /^-\s|\*\s|\+\s/m       // list
      ];
      return this.isMarkdownContent && markdownPatterns.some(pattern => pattern.test(this.content));
    },
  },
};
</script>
