<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import AsistenciaController from '@/actions/App/Http/Controllers/AsistenciaController';
import CursoController from '@/actions/App/Http/Controllers/CursoController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import type { Alumno, Curso } from '@/types';

type AlumnoAsistencia = {
    matricula_id: number;
    alumno: Pick<Alumno, 'id' | 'nombre' | 'apellido'>;
    estado: 'presente' | 'ausente';
};

const props = defineProps<{
    curso: Curso;
    fecha: string;
    alumnos: AlumnoAsistencia[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Cursos', href: CursoController.index() },
            { title: 'Tomar asistencia', href: CursoController.index() },
        ],
    },
});

const selectClass =
    'h-9 w-full max-w-[10rem] rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm';

function onFechaChange(event: Event) {
    const value = (event.target as HTMLInputElement).value;

    router.get(
        AsistenciaController.show.url(props.curso.id, {
            query: { fecha: value },
        }),
    );
}
</script>

<template>
    <Head :title="`Asistencia — ${curso.label}`" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading title="Tomar asistencia" :description="curso.label" />

        <div class="grid max-w-xs gap-2">
            <label for="fecha" class="text-sm font-medium">Fecha</label>
            <input
                id="fecha"
                type="date"
                :value="fecha"
                class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm"
                @change="onFechaChange"
            />
        </div>

        <Form
            v-bind="AsistenciaController.store.form(curso.id)"
            class="max-w-2xl space-y-4"
            v-slot="{ processing }"
        >
            <input type="hidden" name="fecha" :value="fecha" />

            <div class="overflow-hidden rounded-md border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-2 font-medium">Alumno</th>
                            <th class="px-4 py-2 font-medium">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="alumno in alumnos"
                            :key="alumno.matricula_id"
                            class="border-t"
                        >
                            <td class="px-4 py-2">
                                {{ alumno.alumno.apellido }},
                                {{ alumno.alumno.nombre }}
                            </td>
                            <td class="px-4 py-2">
                                <select
                                    :name="`estados[${alumno.matricula_id}]`"
                                    :value="alumno.estado"
                                    :class="selectClass"
                                >
                                    <option value="presente">Presente</option>
                                    <option value="ausente">Ausente</option>
                                </select>
                            </td>
                        </tr>
                        <tr v-if="alumnos.length === 0">
                            <td
                                colspan="2"
                                class="px-4 py-6 text-center text-muted-foreground"
                            >
                                No hay alumnos matriculados en este curso.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing || alumnos.length === 0">
                    Guardar asistencia
                </Button>
                <Link
                    :href="CursoController.index()"
                    class="text-sm text-muted-foreground"
                >
                    Volver
                </Link>
            </div>
        </Form>
    </div>
</template>
