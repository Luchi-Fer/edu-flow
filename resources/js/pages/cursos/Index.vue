<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AsistenciaController from '@/actions/App/Http/Controllers/AsistenciaController';
import CursoController from '@/actions/App/Http/Controllers/CursoController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { CicloLectivo, Curso } from '@/types';

const TODOS = 'todos';

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

const selectedCicloLectivo = computed(
    () => props.cicloLectivoId?.toString() ?? TODOS,
);

function onFilterChange(value: string | null) {
    router.get(
        CursoController.index().url,
        { ciclo_lectivo_id: value },
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

        <Select
            :model-value="selectedCicloLectivo"
            @update:model-value="(value) => onFilterChange(value as string)"
        >
            <SelectTrigger class="w-48">
                <SelectValue placeholder="Ciclo lectivo" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem :value="TODOS">
                    Todos los ciclos lectivos
                </SelectItem>
                <SelectItem
                    v-for="ciclo in ciclosLectivos"
                    :key="ciclo.id"
                    :value="ciclo.id.toString()"
                >
                    {{ ciclo.anio }}
                </SelectItem>
            </SelectContent>
        </Select>

        <p
            v-if="cursos.data.length === 0"
            class="rounded-md border border-dashed p-6 text-center text-muted-foreground"
        >
            No hay cursos que coincidan con el filtro.
        </p>

        <div
            v-else
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <Card v-for="curso in cursos.data" :key="curso.id">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <Badge variant="secondary">
                            {{
                                curso.nivel === 'primaria'
                                    ? 'Primaria'
                                    : 'Secundaria'
                            }}
                        </Badge>
                        <span class="text-xs text-muted-foreground">
                            {{ curso.ciclo_lectivo.anio }}
                        </span>
                    </div>
                    <CardTitle class="text-lg">{{ curso.label }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">
                        Turno: {{ curso.turno ?? '—' }}
                    </p>
                </CardContent>
                <CardFooter class="flex flex-wrap gap-2">
                    <Button
                        v-if="canTomarAsistencia"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="AsistenciaController.show(curso.id)">
                            Tomar asistencia
                        </Link>
                    </Button>

                    <Button
                        v-if="canGestionarCursos"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="CursoController.edit(curso.id)">
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
                                v-bind="CursoController.destroy.form(curso.id)"
                                :options="{ preserveScroll: true }"
                                v-slot="{ processing }"
                            >
                                <DialogHeader class="space-y-3">
                                    <DialogTitle>
                                        ¿Eliminar el curso {{ curso.label }}
                                        ({{ curso.ciclo_lectivo.anio }})?
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
                </CardFooter>
            </Card>
        </div>

        <div v-if="cursos.links.length > 3" class="flex flex-wrap gap-1">
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
                    preserve-scroll
                >
                    <span v-html="link.label" />
                </Link>
            </template>
        </div>
    </div>
</template>
