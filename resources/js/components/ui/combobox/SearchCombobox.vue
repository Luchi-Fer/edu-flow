<script setup lang="ts">
import { Check, Loader2, Search } from '@lucide/vue';
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxPortal,
    ComboboxRoot,
    ComboboxViewport,
} from 'reka-ui';
import { computed, ref } from 'vue';

type Persona = { id: number; nombre: string; apellido: string };
type Opcion = { id: number; label: string };

const props = withDefaults(
    defineProps<{
        modelValue: number | null;
        searchUrl: string;
        placeholder?: string;
        initialLabel?: string | null;
        name?: string;
        allowClear?: boolean;
        emptyText?: string;
    }>(),
    {
        placeholder: 'Buscar...',
        initialLabel: null,
        name: undefined,
        allowClear: false,
        emptyText: 'Sin resultados.',
    },
);

const emit = defineEmits<{
    'update:modelValue': [number | null];
}>();

const options = ref<Opcion[]>([]);
const loading = ref(false);
let debounceTimer: ReturnType<typeof setTimeout> | undefined;

const selectedLabel = computed(() => {
    const enOpciones = options.value.find((o) => o.id === props.modelValue);

    if (enOpciones) {
        return enOpciones.label;
    }

    return props.modelValue !== null ? (props.initialLabel ?? '') : '';
});

async function buscar(search: string) {
    loading.value = true;

    try {
        const url = new URL(props.searchUrl, window.location.origin);

        if (search !== '') {
            url.searchParams.set('search', search);
        }

        const response = await fetch(url.toString(), {
            headers: { Accept: 'application/json' },
        });

        const data = (await response.json()) as Persona[];

        options.value = data.map((persona) => ({
            id: persona.id,
            label: `${persona.apellido}, ${persona.nombre}`,
        }));
    } finally {
        loading.value = false;
    }
}

function onSearchTermChange(value: string) {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => buscar(value), 250);
}

function onOpenChange(open: boolean) {
    if (open) {
        buscar('');
    }
}

function onSelect(value: unknown) {
    emit('update:modelValue', typeof value === 'number' ? value : null);
}
</script>

<template>
    <ComboboxRoot
        :model-value="modelValue"
        ignore-filter
        open-on-click
        :open-on-focus="true"
        @update:model-value="onSelect"
        @update:open="onOpenChange"
    >
        <input v-if="name" type="hidden" :name="name" :value="modelValue ?? ''" />

        <ComboboxAnchor
            class="flex h-9 w-full items-center gap-2 rounded-md border border-input bg-transparent px-3 text-base shadow-xs outline-none focus-within:ring-[3px] focus-within:ring-ring/50 md:text-sm"
        >
            <Search class="size-4 shrink-0 text-muted-foreground" />
            <ComboboxInput
                :display-value="() => selectedLabel"
                :placeholder="placeholder"
                class="h-full w-full bg-transparent outline-none placeholder:text-muted-foreground"
                @update:model-value="onSearchTermChange"
            />
            <Loader2
                v-if="loading"
                class="size-4 shrink-0 animate-spin text-muted-foreground"
            />
        </ComboboxAnchor>

        <ComboboxPortal>
            <ComboboxContent
                class="z-50 max-h-64 w-72 overflow-auto rounded-md border bg-popover p-1 text-popover-foreground shadow-md"
            >
                <ComboboxViewport>
                    <ComboboxItem
                        v-if="allowClear"
                        :value="null"
                        class="relative flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm text-muted-foreground outline-none select-none data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground"
                    >
                        Sin asignar
                    </ComboboxItem>

                    <ComboboxItem
                        v-for="opcion in options"
                        :key="opcion.id"
                        :value="opcion.id"
                        :text-value="opcion.label"
                        class="relative flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 pr-8 text-sm outline-none select-none data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground"
                    >
                        {{ opcion.label }}
                        <span
                            class="absolute right-2 flex size-3.5 items-center justify-center"
                        >
                            <ComboboxItemIndicator>
                                <Check class="size-4" />
                            </ComboboxItemIndicator>
                        </span>
                    </ComboboxItem>

                    <ComboboxEmpty
                        v-if="!loading && options.length === 0"
                        class="px-2 py-1.5 text-sm text-muted-foreground"
                    >
                        {{ emptyText }}
                    </ComboboxEmpty>
                </ComboboxViewport>
            </ComboboxContent>
        </ComboboxPortal>
    </ComboboxRoot>
</template>
