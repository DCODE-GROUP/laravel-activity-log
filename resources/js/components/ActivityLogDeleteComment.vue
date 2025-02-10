<template>
  <div class="flex flex-col flex-1">
    <div class="flex flex-col gap-4 flex-1">
      <div class="p-4 flex-1 flex flex-col gap-2">
        {{ $t("ticket_design.phrases.confirm_delete") }}
      </div>

      <footer
        class="sticky bottom-0 inset-x-0 p-4 border-t border-slate-200 flex gap-2 justify-end bg-white"
      >
        <v-dsg-button type="secondary" @click="close">
          {{ this.i18n.trans("generic.buttons.cancel") }}
        </v-dsg-button>

        <v-dsg-button type="error" @click.prevent="destroy">
          {{ this.i18n.trans("generic.buttons.delete") }}
        </v-dsg-button>
      </footer>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "ActivityLogDeleteComment",
  inject: ["bus", "i18n"],

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
          this.bus.$emit("addComment", data.data);
        })
        .catch(({ error }) => {
          console.error(error);
        });
    },
    close() {
      this.bus.$emit("modalClose");
    },
  },
};
</script>
