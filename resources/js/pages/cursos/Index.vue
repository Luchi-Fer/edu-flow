<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AsistenciaController from '@/actions/App/Http/Controllers/AsistenciaController';
import CursoController from '@/actions/App/Http/Controllers/CursoController';
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
import type { CicloLectivo, Curso } from '@/types';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    cursos: {
        data: Curso[];
        links: PaginationLink[];
    };
    cicloLectivoId: number | null;
    ciclosLectivos: CicloLectivo[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Cursos', href: CursoController.index() }],
    },
});

const page = usePage();
const canGestionarCursos = computed(
    () => page.props.can['gestionar-cursos'] as boolean,
);
const canTomarAsistencia = computed(
    () => page.props.can['tomar-asistencia'] as boolean,
);

function onFilterChange(event: Event) {
    const value = (event.target as HTMLSelectElement).value;

    router.get(
        CursoController.index().url,
        value ? { ciclo_lectivo_id: value } : {},
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <Head title="Cursos" />

    <div class="flex flex-col space-y-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Cursos"
                description="Cursos del colegio por ciclo lectivo"
            />
            <Button v-if="canGestionarCursos" as-child>
                <Link :href="CursoController.create()">Nuevo curso</Link>
            </Button>
        </div>

        <select
            :value="props.cicloLectivoId ?? ''"
            class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm"
            @change="onFilterChange"
        >
            <option value="">Todos los ciclos lectivos</option>
            <option
                v-for="ciclo in ciclosLectivos"
                :key="ciclo.id"
                :value="ciclo.id"
            >
                {{ ciclo.anio }}
            </option>
        </select>

        <div class="overflow-hidden rounded-md border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium">Ciclo lectivo</th>
                        <th class="px-4 py-2 font-medium">Curso</th>
                        <th class="px-4 py-2 font-medium">Turno</th>
                        <th class="px-4 py-2 font-medium">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="curso in cursos.data"
                        :key="curso.id"
                        class="border-t"
                    >
                        <td class="px-4 py-2">
                            {{ curso.ciclo_lectivo.anio }}
                        </td>
                        <td class="px-4 py-2">
                            {{ curso.anio }}° {{ curso.division }}
                        </td>
                        <td class="px-4 py-2">{{ curso.turno ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-2">
                                <Button
                                    v-if="canTomarAsistencia"
                                    as-child
                                    variant="outline"
                                    size="sm"
                                >
                                    <Link
                                        :href="
                                            AsistenciaController.show(curso.id)
                                        "
                                    >
                                        Tomar asistencia
                                    </Link>
                                </Button>

                                <Button
                                    v-if="canGestionarCursos"
                                    as-child
                                    variant="outline"
                                    size="sm"
                                >
                                    <Link
                                        :href="CursoController.edit(curso.id)"
                                    >
                                        Editar
                                    </Link>
                                </Button>

                                <Dialog v-if="canGestionarCursos">
                                    <DialogTrigger as-child>
                                        <Button variant="destructive" size="sm">
                                            Eliminar
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <Form
                                            v-bind="
                                                CursoController.destroy.form(
                                                    curso.id,
                                                )
                                            "
                                            :options="{ preserveScroll: true }"
                                            v-slot="{ processing }"
                                        >
                                            <DialogHeader class="space-y-3">
                                                <DialogTitle>
                                                    ¿Eliminar el curso
                                                    {{ curso.anio }}°
                                                    {{ curso.division }} ({{
                                                        curso.ciclo_lectivo
                                                            .anio
                                                    }})?
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
                    <tr v-if="cursos.data.length === 0">
                        <td
                            colspan="4"
                            class="px-4 py-6 text-center text-muted-foreground"
                        >
                            No hay cursos que coincidan con el filtro.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="cursos.links.length > 3" class="flex gap-1">
            <template v-for="(link, index) in cursos.links" :key="index">
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
