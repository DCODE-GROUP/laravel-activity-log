<template>
  <component :is="icon" :class="classes"></component>
</template>

<script>
import {
  MagnifyingGlassIcon,
  ChevronDownIcon,
  ChevronUpIcon,
  EnvelopeIcon,
} from "@heroicons/vue/24/outline";

export default {
  name: "Icon",
  components: {
    MagnifyingGlassIcon,
    ChevronDownIcon,
    ChevronUpIcon,
  },
  props: {
    icon: {
      type: String,
      required: true,
    },
    classes: {
      type: String,
      required: false,
      default: "w-5 h-5",
    },
  },
};
</script>
