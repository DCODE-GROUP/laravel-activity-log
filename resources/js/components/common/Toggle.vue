<template>
  <div class="flex items-center">
    <button
      type="button"
      class="relative inline-flex h-5 w-10 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
      role="switch"
      :aria-checked="value"
      @click="toggle"
      :class="{ 'bg-indigo-600 focus:ring-indigo-600': value }"
    >
      <span
        aria-hidden="true"
        class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
        :class="{ 'translate-x-5': value, 'translate-x-0': !value }"
      ></span>
    </button>
    <span class="pl-2 text-xs" :class="labelClass">{{ title }}</span>
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
