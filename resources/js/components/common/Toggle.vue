<template>
  <div class="activity__toggle">
    <button
      :aria-checked="value"
      :class="{ 'activity__toggle--button--active': value }"
      class="activity__toggle--button"
      role="switch"
      type="button"
      @click="toggle"
    >
      <span
        :class="{ 'translate-x-5': value, 'translate-x-0': !value }"
        aria-hidden="true"
      ></span>
    </button>
    <span :class="labelClass">{{ title }}</span>
  </div>
</template>

<script>
export default {
  name: "Toggle",
  props: {
    value: {
      required: true,
    },
    title: {
      type: String,
    },
    labelClass: {
      type: String,
      default: null,
    },
  },
  methods: {
    toggle() {
      this.$emit("input", !this.value);
    },
  },
};
</script>
