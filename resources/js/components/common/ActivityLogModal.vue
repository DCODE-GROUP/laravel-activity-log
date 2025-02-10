<template>
  <div :class="{ '-open': isOpen }" class="v-model">
    <transition name="fade">
      <div
        v-if="isOpen"
        class="fixed left-1/2 top-1/2 z-50 flex max-h-screen -translate-x-1/2 -translate-y-1/2 transform flex-col rounded border-tertiary-500 bg-white p-smSpace"
      >
        <div class="absolute right-2xsSpace top-2xsSpace z-30">
          <span
            class="-ml-1 flex h-xlSpace w-xlSpace cursor-pointer items-center justify-center rounded hover:bg-tertiary-100"
            @click="close"
          >
            <v-icon icon="XMarkIcon"></v-icon>
          </span>
        </div>
        <slot>
          <div
            :class="{ 'overflow-y-visible': !scrollable }"
            class="relative max-h-[calc(100vh-40px)] overflow-y-auto"
          >
            <component
              :is="componentName"
              v-if="componentName"
              v-bind="componentData"
              @closeModal="close"
              @confirm="confirm"
            ></component>
            <div v-else class="p-smSpace">
              <div class="py-mdSpace" v-html="componentData"></div>
              <div class="flex justify-center space-x-xsSpace pt-xsSpace">
                <a
                  class="btn-secondary btn-sm"
                  @click="close"
                  v-text="cancelButton"
                ></a>
                <a
                  class="btn-primary btn-sm"
                  v-text="confirmButton"
                  @click.prevent="confirm"
                ></a>
              </div>
            </div>
          </div>
        </slot>
      </div>
    </transition>
  </div>
</template>
<script>
export default {
  name: "ActivityLogModal",
  inject: ["bus"],
  data() {
    return {
      isOpen: false,
      componentName: null,
      componentData: {},
      cancelTitle: null,
      confirmTitle: null,
      scrollable: false,
      isAsyncCallBack: false,
      callback: () => {},
      cancelCallback: () => {},
    };
  },
  created() {
    this.bus.$on("openActivityLogModal", (payload) => {
      this.open();
      this.componentName = payload.componentName;
      this.componentData = payload.componentData;
      this.cancelTitle = payload?.cancelTitle;
      this.confirmTitle = payload?.confirmTitle;
      this.scrollable =
        payload?.scrollable === undefined ? true : payload.scrollable;
      this.isAsyncCallback = payload?.isAsyncCallback ?? false;
      this.callback = payload.callback;
      this.cancelCallback = payload.cancelCallback;
    });
    this.bus.$on("closeActivityLogModal", () => {
      this.close();
    });
  },
  computed: {
    cancelButton() {
      return this.cancelTitle
        ? this.cancelTitle
        : this.$t("generic.buttons.cancel");
    },
    confirmButton() {
      return this.confirmTitle
        ? this.confirmTitle
        : this.$t("generic.buttons.confirm");
    },
  },
  methods: {
    open() {
      this.isOpen = true;
    },
    close() {
      if (this.cancelCallback) {
        this.cancelCallback();
      }
      this.isOpen = false;
    },
    async confirm(data = null) {
      if (this.isAsyncCallback && this.callback) {
        await this.callback(data);
      } else if (this.callback) {
        this.callback(data);
      }
      this.isOpen = false;
    },
  },
};
</script>
<style scoped>
.v-model {
  position: fixed;
  pointer-events: none;
  z-index: 50;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  background-color: transparent;
  transition: background-color 0.3s linear;

  &.-open {
    pointer-events: all;
    background-color: rgba(50, 50, 50, 0.3);
  }
}
</style>
