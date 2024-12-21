<script setup>
import { watch, defineEmits, ref } from "vue";
const props = defineProps({
    open_modal: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: "",
    },
    headerClass: {
        type: String,
        default: "",
    },
    bodyClass: {
        type: String,
        default: "",
    },
    footerClass: {
        type: String,
        default: "",
    },
});
const emits = defineEmits(["close"]);
const show = ref(props.open_modal);
watch(
    () => props.open_modal,
    (newValue) => {
        show.value = newValue;
        if (show.value) {
            document
                .getElementsByTagName("body")[0]
                .classList.add("modal-open");
        } else {
            emits("close");
            document
                .getElementsByTagName("body")[0]
                .classList.remove("modal-open");
        }
    }
);
</script>

<template>
    <div
        class="modal fade"
        :class="[show ? 'show' : '', $attrs.class]"
        :style="{
            display: show ? 'block' : 'none',
        }"
    >
        <div class="modal-dialog" :class="[size]">
            <div class="modal-content">
                <div class="modal-header" :class="[headerClass]">
                    <slot name="header"></slot>
                </div>
                <div class="modal-body" :class="[bodyClass]">
                    <slot name="body"></slot>
                </div>
                <div
                    class="modal-footer"
                    :class="[footerClass]"
                >
                    <slot name="footer"></slot>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped></style>
