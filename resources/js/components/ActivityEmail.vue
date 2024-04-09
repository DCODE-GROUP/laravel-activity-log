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
    </div>
    <div class="pt-smSpace" v-if="isMarkdownContent">
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
    isMarkdownContent: {
      type: Boolean,
      default: false,
    },
  },
};
</script>
