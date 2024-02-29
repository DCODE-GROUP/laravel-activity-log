<template>
  <div class="w-[600px] px-smSpace">
    <div class="border-b pb-mdSpace pt-smSpace">
      <div>
        {{ $t("activity-log.fields.to") }} <span class="font-semibold">{{ to }}</span>
      </div>
      <div>
        {{ $t("activity-log.fields.subject") }} <span class="font-semibold">{{ subject }}</span>
      </div>
    </div>
    <div class="pt-smSpace" v-html="content"></div>
  </div>
</template>
<script>
export default {
  name: "ActivityEmail",
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
  },
};
</script>
