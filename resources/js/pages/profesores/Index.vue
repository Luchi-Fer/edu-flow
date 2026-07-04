<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ProfesorController from '@/actions/App/Http/Controllers/ProfesorController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import type { Profesor } from '@/types';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    profesores: {
        data: Profesor[];
        links: PaginationLink[];
    };
    search: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Profesores', href: ProfesorController.index() },
        ],
    },
});

const search = ref(props.search);

function onSearch() {
    router.get(
        ProfesorController.index().url,
        { search: search.value },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <Head title="Profesores" />

    <div class="flex flex-col space-y-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Profesores"
                description="Gestión de profesores del colegio"
            />
            <Button as-child>
                <Link :href="ProfesorController.create()">Nuevo profesor</Link>
            </Button>
        </div>

        <form class="flex gap-2" @submit.prevent="onSearch">
            <Input
                v-model="search"
                placeholder="Buscar por nombre, apellido o DNI"
                class="max-w-sm"
            />
            <Button type="submit" variant="secondary">Buscar</Button>
        </form>

        <div class="overflow-hidden rounded-md border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium">Apellido y nombre</th>
                        <th class="px-4 py-2 font-medium">DNI</th>
                        <th class="px-4 py-2 font-medium">Email</th>
                        <th class="px-4 py-2 font-medium">Teléfono</th>
                        <th class="px-4 py-2 font-medium">Estado</th>
                        <th class="px-4 py-2 font-medium">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="profesor in profesores.data"
                        :key="profesor.id"
                        class="border-t"
                    >
                        <td class="px-4 py-2">
                            {{ profesor.apellido }}, {{ profesor.nombre }}
                        </td>
                        <td class="px-4 py-2">{{ profesor.dni }}</td>
                        <td class="px-4 py-2">{{ profesor.user.email }}</td>
                        <td class="px-4 py-2">
                            {{ profesor.telefono ?? '—' }}
                        </td>
                        <td class="px-4 py-2">
                            <Badge
                                :variant="
                                    profesor.activo ? 'default' : 'secondary'
                                "
                            >
                                {{ profesor.activo ? 'Activo' : 'Inactivo' }}
                            </Badge>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-2">
                                <Button as-child variant="outline" size="sm">
                                    <Link
                                        :href="
                                            ProfesorController.show(profesor.id)
                                        "
                                    >
                                        Ver
                                    </Link>
                                </Button>

                                <Button as-child variant="outline" size="sm">
                                    <Link
                                        :href="
                                            ProfesorController.edit(profesor.id)
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
                                                ProfesorController.destroy.form(
                                                    profesor.id,
                                                )
                                            "
                                            :options="{ preserveScroll: true }"
                                            v-slot="{ processing }"
                                        >
                                            <DialogHeader class="space-y-3">
                                                <DialogTitle>
                                                    ¿Eliminar a
                                                    {{ profesor.nombre }}
                                                    {{ profesor.apellido }}?
                                                </DialogTitle>
                                                <DialogDescription>
                                                    También se eliminará su
                                                    cuenta de usuario asociada
                                                    ({{ profesor.user.email }}).
                                                </DialogDescription>
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
                    <tr v-if="profesores.data.length === 0">
                        <td
                            colspan="6"
                            class="px-4 py-6 text-center text-muted-foreground"
                        >
                            No hay profesores que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="profesores.links.length > 3" class="flex gap-1">
            <template v-for="(link, index) in profesores.links" :key="index">
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
