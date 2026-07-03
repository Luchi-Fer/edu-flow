<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import CicloLectivoController from '@/actions/App/Http/Controllers/CicloLectivoController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
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
import type { CicloLectivo } from '@/types';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

defineProps<{
    ciclosLectivos: {
        data: CicloLectivo[];
        links: PaginationLink[];
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Ciclos Lectivos',
                href: CicloLectivoController.index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Ciclos Lectivos" />

    <div class="flex flex-col space-y-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Ciclos Lectivos"
                description="Años lectivos del colegio"
            />
            <Button as-child>
                <Link :href="CicloLectivoController.create()">
                    Nuevo ciclo lectivo
                </Link>
            </Button>
        </div>

        <div class="overflow-hidden rounded-md border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium">Año</th>
                        <th class="px-4 py-2 font-medium">Inicio</th>
                        <th class="px-4 py-2 font-medium">Fin</th>
                        <th class="px-4 py-2 font-medium">Estado</th>
                        <th class="px-4 py-2 font-medium">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="ciclo in ciclosLectivos.data"
                        :key="ciclo.id"
                        class="border-t"
                    >
                        <td class="px-4 py-2">{{ ciclo.anio }}</td>
                        <td class="px-4 py-2">{{ ciclo.fecha_inicio }}</td>
                        <td class="px-4 py-2">{{ ciclo.fecha_fin }}</td>
                        <td class="px-4 py-2">
                            <Badge
                                :variant="
                                    ciclo.activo ? 'default' : 'secondary'
                                "
                            >
                                {{ ciclo.activo ? 'Activo' : 'Inactivo' }}
                            </Badge>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-2">
                                <Button as-child variant="outline" size="sm">
                                    <Link
                                        :href="
                                            CicloLectivoController.edit(
                                                ciclo.id,
                                            )
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
                                                CicloLectivoController.destroy.form(
                                                    ciclo.id,
                                                )
                                            "
                                            :options="{ preserveScroll: true }"
                                            v-slot="{ processing }"
                                        >
                                            <DialogHeader class="space-y-3">
                                                <DialogTitle>
                                                    ¿Eliminar el ciclo lectivo
                                                    {{ ciclo.anio }}?
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
                    <tr v-if="ciclosLectivos.data.length === 0">
                        <td
                            colspan="5"
                            class="px-4 py-6 text-center text-muted-foreground"
                        >
                            No hay ciclos lectivos cargados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="ciclosLectivos.links.length > 3" class="flex gap-1">
            <template
                v-for="(link, index) in ciclosLectivos.links"
                :key="index"
            >
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
