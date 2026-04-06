<template>
    <div
        class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500"
    >
        <div
            class="flex items-center gap-0.5 px-2 py-1.5 border-b border-gray-200 dark:border-gray-700"
        >
            <button
                type="button"
                @click="editor?.chain().focus().toggleBold().run()"
                :class="[
                    'p-1.5 rounded-md text-sm font-medium transition-colors',
                    editor?.isActive('bold')
                        ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white'
                        : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M6 4h8a4 4 0 0 1 2.83 6.83A4 4 0 0 1 15 18H6V4Zm2 2v4h6a2 2 0 1 0 0-4H8Zm0 6v4h7a2 2 0 1 0 0-4H8Z"
                    />
                </svg>
            </button>
            <button
                type="button"
                @click="editor?.chain().focus().toggleItalic().run()"
                :class="[
                    'p-1.5 rounded-md text-sm transition-colors',
                    editor?.isActive('italic')
                        ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white'
                        : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M10 4h8v2h-2.21l-3.42 12H15v2H7v-2h2.21l3.42-12H10V4Z" />
                </svg>
            </button>
            <div class="w-px h-4 bg-gray-200 dark:bg-gray-700 mx-1" />
            <button
                type="button"
                @click="setLink"
                :class="[
                    'p-1.5 rounded-md text-sm transition-colors',
                    editor?.isActive('link')
                        ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white'
                        : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M17 7h-4v2h4a3 3 0 1 1 0 6h-4v2h4a5 5 0 0 0 0-10Zm-6 8H7a3 3 0 1 1 0-6h4V7H7a5 5 0 0 0 0 10h4v-2Zm-3-4h8v2H8v-2Z"
                    />
                </svg>
            </button>
        </div>
        <EditorContent :editor="editor" />
    </div>
</template>

<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import Document from '@tiptap/extension-document'
import Paragraph from '@tiptap/extension-paragraph'
import Text from '@tiptap/extension-text'
import Bold from '@tiptap/extension-bold'
import Italic from '@tiptap/extension-italic'
import Link from '@tiptap/extension-link'
import History from '@tiptap/extension-history'
import CharacterCount from '@tiptap/extension-character-count'
import { watch } from 'vue'

const props = defineProps({
    modelValue: { type: String, default: '' },
    maxlength: { type: Number, default: 2000 },
    placeholder: { type: String, default: '' }
})

const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        Document,
        Paragraph,
        Text,
        Bold,
        Italic,
        Link.configure({
            openOnClick: false,
            HTMLAttributes: { class: 'underline font-medium text-blue-500 dark:text-blue-400' }
        }),
        History,
        CharacterCount.configure({ limit: props.maxlength })
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm dark:prose-invert max-w-none px-3 py-2 min-h-[6rem] focus:outline-none text-gray-900 dark:text-white'
        }
    },
    onUpdate({ editor }) {
        const html = editor.isEmpty ? '' : editor.getHTML()
        emit('update:modelValue', html)
    }
})

watch(
    () => props.modelValue,
    (val) => {
        if (editor.value && editor.value.getHTML() !== val) {
            editor.value.commands.setContent(val || '', false)
        }
    }
)

function setLink() {
    const prev = editor.value.getAttributes('link').href
    const url = window.prompt('URL', prev)
    if (url === null) return
    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run()
        return
    }
    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}
</script>
