<script setup lang="ts">
import { ref, watch, onMounted, type PropType } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps({
    modelValue: { type: String, default: '' },
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const editorRef = ref<HTMLDivElement>();
const content = ref(props.modelValue);

watch(content, (val) => emit('update:modelValue', val));
watch(() => props.modelValue, (val) => {
    content.value = val;
    if (editorRef.value && val !== editorRef.value.innerHTML) {
        editorRef.value.innerHTML = val;
    }
});

onMounted(() => {
    if (editorRef.value && props.modelValue) {
        editorRef.value.innerHTML = props.modelValue;
    }
});

function exec(cmd: string, value?: string) {
    document.execCommand(cmd, false, value);
    editorRef.value?.focus();
    syncContent();
}

function syncContent() {
    content.value = editorRef.value?.innerHTML ?? '';
}

const headingOpen = ref(false);

function toolbarClass(active = false) {
    return [
        'inline-flex h-8 items-center rounded-md px-2 text-xs font-bold leading-[1] transition-colors',
        active
            ? 'bg-[#e5e5e0] text-[#000000]'
            : 'text-[#000000] hover:bg-[#f6f6f3]',
    ];
}
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-[#91918c] bg-white focus-within:border-black focus-within:ring-[3px] focus-within:ring-[#435ee5]/30">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-1 border-b border-[#e5e5e0] px-3 py-2">
            <button type="button" @click="exec('bold')" :class="toolbarClass()">
                <strong>B</strong>
            </button>
            <button type="button" @click="exec('italic')" :class="toolbarClass()">
                <em>I</em>
            </button>
            <button type="button" @click="exec('underline')" :class="toolbarClass()">
                <u>U</u>
            </button>
            <span class="mx-1 h-4 w-px bg-[#dadad3]" />
            <button type="button" @click="exec('insertUnorderedList')" :class="toolbarClass()">
                &bull; List
            </button>
            <button type="button" @click="exec('insertOrderedList')" :class="toolbarClass()">
                1. List
            </button>
            <span class="mx-1 h-4 w-px bg-[#dadad3]" />
            <div class="relative">
                <button type="button" @click="headingOpen = !headingOpen" :class="toolbarClass()">
                    H ▼
                </button>
                <div v-if="headingOpen" class="absolute left-0 top-full z-10 mt-1 rounded-xl border border-[#dadad3] bg-white p-1 shadow-lg">
                    <button type="button" @click="exec('formatBlock', 'h2'); headingOpen = false" class="block w-full rounded-lg px-3 py-1.5 text-left text-xs hover:bg-[#f6f6f3]">Heading 2</button>
                    <button type="button" @click="exec('formatBlock', 'h3'); headingOpen = false" class="block w-full rounded-lg px-3 py-1.5 text-left text-xs hover:bg-[#f6f6f3]">Heading 3</button>
                    <button type="button" @click="exec('formatBlock', 'p'); headingOpen = false" class="block w-full rounded-lg px-3 py-1.5 text-left text-xs hover:bg-[#f6f6f3]">Paragraph</button>
                </div>
            </div>
        </div>
        <!-- Editor content -->
        <div
            ref="editorRef"
            contenteditable="true"
            class="min-h-[120px] px-4 py-3 text-sm leading-[1.5] text-[#000000] outline-none empty:before:text-[#91918c] empty:before:content-[attr(data-placeholder)]"
            data-placeholder="Deskripsi produk..."
            @input="syncContent"
            @blur="headingOpen = false"
        />
    </div>
</template>
