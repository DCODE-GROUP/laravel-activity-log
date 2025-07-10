<template>
  <component :is="icon" :class="classes"></component>
</template>

<script>
import {
  MagnifyingGlassIcon,
  ChevronDownIcon,
  ChevronUpIcon,
  EnvelopeIcon,
  ChatBubbleBottomCenterIcon,
  ChatBubbleLeftEllipsisIcon,
  ChatBubbleLeftRightIcon,
  BellIcon,
  PencilIcon,
  ArrowPathIcon,
  EllipsisHorizontalIcon,
  TrashIcon,
  PhoneIcon,
  ArrowDownTrayIcon,
  XMarkIcon,
} from "@heroicons/vue/24/outline";

export default {
  name: "Icon",
  components: {
    MagnifyingGlassIcon,
    ChevronDownIcon,
    ChevronUpIcon,
    EnvelopeIcon,
    ChatBubbleBottomCenterIcon,
    ChatBubbleLeftEllipsisIcon,
    ChatBubbleLeftRightIcon,
    BellIcon,
    PencilIcon,
    PhoneIcon,
    ArrowPathIcon,
    ArrowDownTrayIcon,
    EllipsisHorizontalIcon,
    TrashIcon,
    XMarkIcon,
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
