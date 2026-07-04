<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ProfesorController from '@/actions/App/Http/Controllers/ProfesorController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Profesor } from '@/types';

type Asignacion = {
    id: number;
    ciclo_lectivo: number;
    curso: string;
    materia: string;
};

defineProps<{
    profesor: Profesor;
    asignaciones: Asignacion[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Profesores', href: ProfesorController.index() },
            { title: 'Ver profesor', href: ProfesorController.index() },
        ],
    },
});
</script>

<template>
    <Head :title="`${profesor.nombre} ${profesor.apellido}`" />

    <div class="flex flex-col space-y-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                :title="`${profesor.nombre} ${profesor.apellido}`"
                :description="profesor.user.email"
            />
            <Button as-child variant="outline">
                <Link :href="ProfesorController.edit(profesor.id)">
                    Editar
                </Link>
            </Button>
        </div>

        <div class="grid max-w-2xl grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-muted-foreground">DNI</p>
                <p>{{ profesor.dni }}</p>
            </div>
            <div>
                <p class="text-muted-foreground">Teléfono</p>
                <p>{{ profesor.telefono ?? '—' }}</p>
            </div>
            <div>
                <p class="text-muted-foreground">Estado</p>
                <Badge :variant="profesor.activo ? 'default' : 'secondary'">
                    {{ profesor.activo ? 'Activo' : 'Inactivo' }}
                </Badge>
            </div>
        </div>

        <div class="space-y-2">
            <Heading
                title="Materias y cursos a cargo"
                description="Un profesor puede dar distintas materias en distintos cursos"
            />

            <div class="overflow-hidden rounded-md border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-2 font-medium">Ciclo lectivo</th>
                            <th class="px-4 py-2 font-medium">Curso</th>
                            <th class="px-4 py-2 font-medium">Materia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="asignacion in asignaciones"
                            :key="asignacion.id"
                            class="border-t"
                        >
                            <td class="px-4 py-2">
                                {{ asignacion.ciclo_lectivo }}
                            </td>
                            <td class="px-4 py-2">{{ asignacion.curso }}</td>
                            <td class="px-4 py-2">{{ asignacion.materia }}</td>
                        </tr>
                        <tr v-if="asignaciones.length === 0">
                            <td
                                colspan="3"
                                class="px-4 py-6 text-center text-muted-foreground"
                            >
                                Este profesor no tiene materias asignadas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Link
            :href="ProfesorController.index()"
            class="text-sm text-muted-foreground"
        >
            Volver
        </Link>
    </div>
</template>
