<script setup>
import { watch, defineEmits, ref, onMounted, onBeforeUnmount } from "vue";
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
    closeEsc: {
        type: Boolean,
        default: false,
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
            if (modal.value) {
                window.addEventListener("keyup", handleKeyup);
            }
        } else {
            emits("close");
            document
                .getElementsByTagName("body")[0]
                .classList.remove("modal-open");
            window.removeEventListener("keyup", handleKeyup);
        }
    }
);

const modal = ref(null);

const handleKeyup = (e) => {
    if (e.key === "Escape" && show.value) {
        if (props.closeEsc) {
            emits("close");
        } else {
            triggerShake();
        }
    }
};

const isShaking = ref(false);

const clickModal = (e) => {
    if (e.target == modal.value) {
        triggerShake();
    }
};

const triggerShake = () => {
    if (!props.closeEsc) {
        if (!isShaking.value) {
            isShaking.value = true;
            setTimeout(() => {
                isShaking.value = false;
            }, 500);
        }
    }
};

onMounted(() => {});

onBeforeUnmount(() => {});
</script>

<template>
    <div
        class="modal fade"
        :class="[show ? 'show' : '', $attrs.class]"
        :style="{
            display: show ? 'block' : 'none',
        }"
        ref="modal"
        @click="clickModal"
    >
        <div class="modal-dialog" :class="[size]">
            <div class="modal-content" :class="{ shake: isShaking }">
                <div class="modal-header" :class="[headerClass]">
                    <slot name="header"></slot>
                </div>
                <div class="modal-body" :class="[bodyClass]">
                    <slot name="body"></slot>
                </div>
                <div class="modal-footer" :class="[footerClass]">
                    <slot name="footer"></slot>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
@keyframes shake {
    0% {
        transform: translateX(0);
    }
    25% {
        transform: translateX(-10px);
    }
    50% {
        transform: translateX(10px);
    }
    75% {
        transform: translateX(-10px);
    }
    100% {
        transform: translateX(0);
    }
}

.shake {
    animation: shake 0.5s ease-in-out;
}
</style>
