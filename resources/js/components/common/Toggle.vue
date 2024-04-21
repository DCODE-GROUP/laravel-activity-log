<template>
  <div class="activity__toggle">
    <button
      type="button"
      class="activity__toggle--button"
      role="switch"
      :aria-checked="value"
      @click="toggle"
      :class="{ 'activity__toggle--button--active': value }"
    >
      <span
        aria-hidden="true"
        :class="{ 'translate-x-5': value, 'translate-x-0': !value }"
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
