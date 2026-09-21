<script setup lang="ts">
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import Underline from '@tiptap/extension-underline';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import {
    Bold,
    Heading2,
    Italic,
    Link2,
    List,
    ListOrdered,
    Pilcrow,
    Quote,
    Redo2,
    Strikethrough,
    UnderlineIcon,
    Undo2,
    Unlink,
} from '@lucide/vue';
import { onBeforeUnmount, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        name: string;
        placeholder?: string;
    }>(),
    {
        modelValue: '',
        placeholder: 'Start writing...',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const editor = useEditor({
    content: props.modelValue ?? '',
    extensions: [
        StarterKit,
        Underline,
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: 'text-primary underline underline-offset-2',
            },
        }),
        Placeholder.configure({ placeholder: props.placeholder }),
    ],
    editorProps: {
        attributes: {
            class: 'min-h-40 px-3 py-2 text-sm focus:outline-none',
        },
    },
    onUpdate: ({ editor: currentEditor }) => {
        emit('update:modelValue', currentEditor.getHTML());
    },
});

watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && editor.value.getHTML() !== (value ?? '')) {
            editor.value.commands.setContent(value ?? '', {
                emitUpdate: false,
            });
        }
    },
);

const setLink = () => {
    if (!editor.value) return;

    const previousUrl = editor.value.getAttributes('link').href as
        string | undefined;
    const url = window.prompt('Enter link URL', previousUrl ?? 'https://');

    if (url === null) return;
    if (!url.trim()) {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }

    editor.value
        .chain()
        .focus()
        .extendMarkRange('link')
        .setLink({ href: url.trim() })
        .run();
};

onBeforeUnmount(() => editor.value?.destroy());
</script>

<template>
    <div class="overflow-hidden rounded-md border border-input bg-background">
        <div
            v-if="editor"
            class="flex flex-wrap items-center gap-1 border-b bg-muted/30 p-1.5"
            role="toolbar"
            aria-label="Text formatting"
        >
            <button
                type="button"
                class="editor-button"
                :class="{
                    'editor-button-active': editor.isActive('paragraph'),
                }"
                title="Paragraph"
                @click="editor.chain().focus().setParagraph().run()"
            >
                <Pilcrow class="size-4" />
            </button>
            <button
                type="button"
                class="editor-button"
                :class="{
                    'editor-button-active': editor.isActive('heading', {
                        level: 2,
                    }),
                }"
                title="Heading"
                @click="
                    editor.chain().focus().toggleHeading({ level: 2 }).run()
                "
            >
                <Heading2 class="size-4" />
            </button>
            <span class="mx-0.5 h-5 w-px bg-border" />
            <button
                v-for="control in [
                    { title: 'Bold', icon: Bold, mark: 'bold' },
                    { title: 'Italic', icon: Italic, mark: 'italic' },
                    {
                        title: 'Underline',
                        icon: UnderlineIcon,
                        mark: 'underline',
                    },
                    {
                        title: 'Strikethrough',
                        icon: Strikethrough,
                        mark: 'strike',
                    },
                ]"
                :key="control.mark"
                type="button"
                class="editor-button"
                :class="{
                    'editor-button-active': editor.isActive(control.mark),
                }"
                :title="control.title"
                @click="editor.chain().focus().toggleMark(control.mark).run()"
            >
                <component :is="control.icon" class="size-4" />
            </button>
            <span class="mx-0.5 h-5 w-px bg-border" />
            <button
                type="button"
                class="editor-button"
                :class="{
                    'editor-button-active': editor.isActive('bulletList'),
                }"
                title="Bullet list"
                @click="editor.chain().focus().toggleBulletList().run()"
            >
                <List class="size-4" />
            </button>
            <button
                type="button"
                class="editor-button"
                :class="{
                    'editor-button-active': editor.isActive('orderedList'),
                }"
                title="Numbered list"
                @click="editor.chain().focus().toggleOrderedList().run()"
            >
                <ListOrdered class="size-4" />
            </button>
            <button
                type="button"
                class="editor-button"
                :class="{
                    'editor-button-active': editor.isActive('blockquote'),
                }"
                title="Blockquote"
                @click="editor.chain().focus().toggleBlockquote().run()"
            >
                <Quote class="size-4" />
            </button>
            <span class="mx-0.5 h-5 w-px bg-border" />
            <button
                type="button"
                class="editor-button"
                :class="{ 'editor-button-active': editor.isActive('link') }"
                title="Add link"
                @click="setLink"
            >
                <Link2 class="size-4" />
            </button>
            <button
                type="button"
                class="editor-button"
                title="Remove link"
                :disabled="!editor.isActive('link')"
                @click="editor.chain().focus().unsetLink().run()"
            >
                <Unlink class="size-4" />
            </button>
            <span class="mx-0.5 h-5 w-px bg-border" />
            <button
                type="button"
                class="editor-button"
                title="Undo"
                :disabled="!editor.can().chain().focus().undo().run()"
                @click="editor.chain().focus().undo().run()"
            >
                <Undo2 class="size-4" />
            </button>
            <button
                type="button"
                class="editor-button"
                title="Redo"
                :disabled="!editor.can().chain().focus().redo().run()"
                @click="editor.chain().focus().redo().run()"
            >
                <Redo2 class="size-4" />
            </button>
        </div>

        <EditorContent :editor="editor" />
        <input
            :name="name"
            type="hidden"
            :value="editor?.getHTML() ?? modelValue ?? ''"
        />
    </div>
</template>

<style scoped>
.editor-button {
    display: inline-flex;
    width: 2rem;
    height: 2rem;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;
    color: var(--muted-foreground);
    transition:
        color 150ms,
        background-color 150ms;
}

.editor-button:hover:not(:disabled),
.editor-button-active {
    background: var(--accent);
    color: var(--accent-foreground);
}

.editor-button:disabled {
    cursor: not-allowed;
    opacity: 0.4;
}

:deep(.tiptap p.is-editor-empty:first-child::before) {
    height: 0;
    color: var(--muted-foreground);
    content: attr(data-placeholder);
    float: left;
    pointer-events: none;
}

:deep(.tiptap h2) {
    margin: 0.75rem 0 0.25rem;
    font-size: 1.25rem;
    font-weight: 600;
}

:deep(.tiptap ul) {
    margin: 0.5rem 0;
    list-style: disc;
    padding-left: 1.5rem;
}

:deep(.tiptap ol) {
    margin: 0.5rem 0;
    list-style: decimal;
    padding-left: 1.5rem;
}

:deep(.tiptap blockquote) {
    margin: 0.75rem 0;
    border-left: 3px solid var(--border);
    padding-left: 0.75rem;
    color: var(--muted-foreground);
}
</style>
