<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import MateriaController from '@/actions/App/Http/Controllers/MateriaController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import type { Materia } from '@/types';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    materias: {
        data: Materia[];
        links: PaginationLink[];
    };
    search: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Materias', href: MateriaController.index() }],
    },
});

const search = ref(props.search);

function onSearch() {
    router.get(
        MateriaController.index().url,
        { search: search.value },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <Head title="Materias" />

    <div class="flex flex-col space-y-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Materias"
                description="Gestión de materias del colegio"
            />
            <Button as-child>
                <Link :href="MateriaController.create()">Nueva materia</Link>
            </Button>
        </div>

        <form class="flex gap-2" @submit.prevent="onSearch">
            <Input
                v-model="search"
                placeholder="Buscar por nombre"
                class="max-w-sm"
            />
            <Button type="submit" variant="secondary">Buscar</Button>
        </form>

        <div class="overflow-hidden rounded-md border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium">Nombre</th>
                        <th class="px-4 py-2 font-medium">Descripción</th>
                        <th class="px-4 py-2 font-medium">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="materia in materias.data"
                        :key="materia.id"
                        class="border-t"
                    >
                        <td class="px-4 py-2">{{ materia.nombre }}</td>
                        <td class="px-4 py-2">
                            {{ materia.descripcion ?? '—' }}
                        </td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-2">
                                <Button as-child variant="outline" size="sm">
                                    <Link
                                        :href="
                                            MateriaController.edit(materia.id)
                                        "
                                    >
                                        Editar
                                    </Link>
                                </Button>

                                <Dialog>
                                    <DialogTrigger as-child>
                                        <Button variant="destructive" size="sm">
                                            Eliminar
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <Form
                                            v-bind="
                                                MateriaController.destroy.form(
                                                    materia.id,
                                                )
                                            "
                                            :options="{ preserveScroll: true }"
                                            v-slot="{ processing }"
                                        >
                                            <DialogHeader class="space-y-3">
                                                <DialogTitle>
                                                    ¿Eliminar la materia
                                                    {{ materia.nombre }}?
                                                </DialogTitle>
                                            </DialogHeader>

                                            <DialogFooter class="mt-6 gap-2">
                                                <DialogClose as-child>
                                                    <Button variant="secondary">
                                                        Cancelar
                                                    </Button>
                                                </DialogClose>

                                                <Button
                                                    type="submit"
                                                    variant="destructive"
                                                    :disabled="processing"
                                                >
                                                    Eliminar
                                                </Button>
                                            </DialogFooter>
                                        </Form>
                                    </DialogContent>
                                </Dialog>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="materias.data.length === 0">
                        <td
                            colspan="3"
                            class="px-4 py-6 text-center text-muted-foreground"
                        >
                            No hay materias que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="materias.links.length > 3" class="flex gap-1">
            <template v-for="(link, index) in materias.links" :key="index">
                <span
                    v-if="!link.url"
                    class="rounded-md px-3 py-1 text-sm text-muted-foreground"
                    v-html="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    class="rounded-md px-3 py-1 text-sm"
                    :class="
                        link.active
                            ? 'bg-primary text-primary-foreground'
                            : 'hover:bg-muted'
                    "
                >
                    <span v-html="link.label" />
                </Link>
            </template>
        </div>
    </div>
</template>
