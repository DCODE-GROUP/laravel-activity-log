<template>
  <div class="flex flex-col flex-1">
    <div class="flex flex-col gap-4 flex-1">
      <div class="p-4 flex-1 flex flex-col gap-2">
        {{ $t("activity-log.headings.confirm_delete_comment") }}
      </div>

      <footer
        class="sticky bottom-0 inset-x-0 p-4 border-t border-slate-200 flex gap-2 justify-end bg-white"
      >
        <v-dsg-button type="secondary" @click="close">
          {{ $t("generic.buttons.cancel") }}
        </v-dsg-button>

        <v-dsg-button type="error" @click.prevent="destroy">
          {{ $t("generic.buttons.delete") }}
        </v-dsg-button>
      </footer>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "ActivityLogDeleteComment",
  inject: ["bus"],

  props: {
    endpoint: {
      type: String,
      required: true,
    },
  },

  data() {
    return {};
  },

  methods: {
    async destroy() {
      axios
        .delete(this.endpoint)
        .then(({ data }) => {
          this.bus.$emit("refreshActivityLog");
          this.bus.$emit("closeActivityLogModal");
        })
        .catch(({ error }) => {
          console.error(error);
        });
    },
    close() {
      this.bus.$emit("closeActivityLogModal");
    },
  },
};
</script>
