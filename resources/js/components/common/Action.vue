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
            <li @click="editItem">
              <button>
                <icon icon="PencilIcon"></icon>
                <span>{{ $t("activity-log.buttons.edit") }}</span>
              </button>
            </li>
            <li @click="openDeleteModal">
              <button class="button-delete">
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
import Icon from "./Icon.vue";
import ActivityLogModal from "../ActivityLogModal.vue";

export default {
  name: "Action",
  inject: ["bus"],
  components: { ActivityLogModal, Icon },
  props: {
    modalEvent: {
      type: String,
      default: "openActivityLogModal",
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
        componentName: "ActivityLogDeleteComment",
        componentData: {
          endpoint: activity.delete_comment_endpoint,
        },
        // callback: this.deleteItem,
      });
    },
    // deleteItem() {
    //   axios
    //     .delete(this.getUrl + "/comment/" + this.activity.id)
    //     .then(({ data }) => {
    //       this.$emit("addComment", data.data);
    //     })
    //     .catch(console.error);
    // },
    editItem() {
      this.$emit("editComment", this.activity.id);
    },
  },
};
</script>
