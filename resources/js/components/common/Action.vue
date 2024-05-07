<template>
  <div v-click-outside="onClickOutside" class="activity__action">
    <button
        :class="{ active: active }"
        class="activity__action--button"
        @click.prevent="active = !active"
    >
      <icon icon="EllipsisHorizontalIcon"></icon>
    </button>
    <transition name="fade ">
      <div v-if="active" class="activity__action--panel">
        <div class="panel__content">
          <ul class="panel__content--list">
            <li>
              <button @click="confirm">
                <icon icon="PencilIcon"></icon>
                <span>{{ $t("activity-log.buttons.edit") }}</span>
              </button>
            </li>
            <li>
              <button @click="openDeleteModal" class="button-delete">
                <icon icon="TrashIcon"></icon>
                <span>{{ $t("activity-log.buttons.delete") }}</span>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";
import Icon from "./Icon.vue";

export default {
  name: "Action",
  inject: ["bus"],
  components: {Icon},
  props: {
    modalEvent: {
      type: String,
      default: "openModal",
    },
    activity: {
      type: Object,
      required: true,
    },
    getUrl: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      active: false,
    };
  },
  methods: {
    onClickOutside(event) {
      this.active = false;
    },
    openDeleteModal(activity) {
      this.bus.$emit(this.modalEvent, {
        componentData: `<h5>${this.$t("activity-log.words.delete_note")}</h5><br/><span>${this.$t("activity-log.words.delete_note_content")}</span>`,
        callback: this.deleteItem,
      });
    },
    deleteItem() {
      axios.delete(this.getUrl + '/comment/' + this.activity.id).then(({data}) => {
        this.$emit("addComment", data.data);
      }).catch(console.error);
    }
  },
};
</script>

