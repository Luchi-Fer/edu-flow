<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CursoAlumnoController from '@/actions/App/Http/Controllers/CursoAlumnoController';
import CursoController from '@/actions/App/Http/Controllers/CursoController';
import CursoMateriaController from '@/actions/App/Http/Controllers/CursoMateriaController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
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
import { Label } from '@/components/ui/label';
import { useDateFormat } from '@/composables/useDateFormat';
import { ETIQUETAS_ANIO_POR_NIVEL } from '@/lib/nivelEducativo';
import type { Alumno, CicloLectivo, Curso, Materia, Profesor } from '@/types';

type MateriaAsignada = Pick<Materia, 'id' | 'nombre'> & {
    pivot: { profesor_id: number | null };
};

type ProfesorOption = Pick<Profesor, 'id' | 'nombre' | 'apellido'>;

type AlumnoOption = Pick<Alumno, 'id' | 'nombre' | 'apellido'>;

type AlumnoMatriculado = AlumnoOption & {
    pivot: {
        fecha_matriculacion: string;
        estado: 'activo' | 'baja' | 'egresado';
    };
};

const props = defineProps<{
    curso: Curso & {
        materias: MateriaAsignada[];
        alumnos: AlumnoMatriculado[];
    };
    ciclosLectivos: CicloLectivo[];
    materiasDisponibles: Pick<Materia, 'id' | 'nombre'>[];
    profesoresActivos: ProfesorOption[];
    alumnosDisponibles: AlumnoOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Cursos', href: CursoController.index() },
            { title: 'Editar curso', href: CursoController.index() },
        ],
    },
});

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm';

function onProfesorChange(materiaId: number, event: Event) {
    const value = (event.target as HTMLSelectElement).value;

    router.patch(
        CursoMateriaController.update.url([props.curso.id, materiaId]),
        { profesor_id: value || null },
        { preserveScroll: true },
    );
}

function onEstadoChange(alumnoId: number, event: Event) {
    const value = (event.target as HTMLSelectElement).value;

    router.patch(
        CursoAlumnoController.update.url([props.curso.id, alumnoId]),
        { estado: value },
        { preserveScroll: true },
    );
}

const today = new Date().toISOString().slice(0, 10);

const { formatDate } = useDateFormat();

const nivel = ref<'primaria' | 'secundaria'>(props.curso.nivel);
const etiquetasAnio = computed(() => ETIQUETAS_ANIO_POR_NIVEL[nivel.value]);
</script>

<template>
    <Head :title="`Editar curso ${curso.label}`" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading title="Editar curso" :description="curso.label" />

        <Form
            v-bind="CursoController.update.form(curso.id)"
            class="max-w-5xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid grid-cols-5 gap-4">
                <div class="grid gap-2">
                    <Label for="ciclo_lectivo_id">Ciclo lectivo</Label>
                    <select
                        id="ciclo_lectivo_id"
                        name="ciclo_lectivo_id"
                        required
                        :value="curso.ciclo_lectivo_id"
                        :class="selectClass"
                    >
                        <option
                            v-for="ciclo in props.ciclosLectivos"
                            :key="ciclo.id"
                            :value="ciclo.id"
                        >
                            {{ ciclo.anio }}
                        </option>
                    </select>
                    <InputError :message="errors.ciclo_lectivo_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="nivel">Nivel</Label>
                    <select
                        id="nivel"
                        name="nivel"
                        v-model="nivel"
                        required
                        :class="selectClass"
                    >
                        <option value="primaria">Primaria</option>
                        <option value="secundaria">Secundaria</option>
                    </select>
                    <InputError :message="errors.nivel" />
                </div>

                <div class="grid gap-2">
                    <Label for="anio">Año</Label>
                    <select
                        id="anio"
                        name="anio"
                        required
                        :value="curso.anio"
                        :class="selectClass"
                    >
                        <option
                            v-for="(etiqueta, indice) in etiquetasAnio"
                            :key="etiqueta"
                            :value="indice + 1"
                        >
                            {{ etiqueta }}
                        </option>
                    </select>
                    <InputError :message="errors.anio" />
                </div>

                <div class="grid gap-2">
                    <Label for="division">División</Label>
                    <Input
                        id="division"
                        name="division"
                        :default-value="curso.division"
                        required
                    />
                    <InputError :message="errors.division" />
                </div>

                <div class="grid gap-2">
                    <Label for="turno">Turno</Label>
                    <select
                        id="turno"
                        name="turno"
                        :value="curso.turno ?? ''"
                        :class="selectClass"
                    >
                        <option value="">Sin especificar</option>
                        <option value="mañana">Mañana</option>
                        <option value="tarde">Tarde</option>
                        <option value="noche">Noche</option>
                    </select>
                    <InputError :message="errors.turno" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Guardar</Button>
                <Link
                    :href="CursoController.index()"
                    class="text-sm text-muted-foreground"
                >
                    Cancelar
                </Link>
            </div>
        </Form>

        <div class="max-w-5xl space-y-4 border-t pt-6">
            <Heading
                variant="small"
                title="Materias asignadas"
                description="Materias que se dictan en este curso y quién las da"
            />

            <div class="overflow-hidden rounded-md border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-2 font-medium">Materia</th>
                            <th class="px-4 py-2 font-medium">Profesor</th>
                            <th class="px-4 py-2 font-medium">
                                <span class="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="materia in curso.materias"
                            :key="materia.id"
                            class="border-t"
                        >
                            <td class="px-4 py-2">{{ materia.nombre }}</td>
                            <td class="px-4 py-2">
                                <select
                                    :value="materia.pivot.profesor_id ?? ''"
                                    class="h-8 w-full max-w-xs rounded-md border border-input bg-transparent px-2 text-sm outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                    @change="
                                        onProfesorChange(materia.id, $event)
                                    "
                                >
                                    <option value="">Sin asignar</option>
                                    <option
                                        v-for="profesor in profesoresActivos"
                                        :key="profesor.id"
                                        :value="profesor.id"
                                    >
                                        {{ profesor.apellido }},
                                        {{ profesor.nombre }}
                                    </option>
                                </select>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <Dialog>
                                    <DialogTrigger as-child>
                                        <Button variant="destructive" size="sm">
                                            Quitar
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <Form
                                            v-bind="
                                                CursoMateriaController.destroy.form(
                                                    [curso.id, materia.id],
                                                )
                                            "
                                            :options="{ preserveScroll: true }"
                                            v-slot="{ processing: removing }"
                                        >
                                            <DialogHeader class="space-y-3">
                                                <DialogTitle>
                                                    ¿Quitar
                                                    {{ materia.nombre }} de este
                                                    curso?
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
                                                    :disabled="removing"
                                                >
                                                    Quitar
                                                </Button>
                                            </DialogFooter>
                                        </Form>
                                    </DialogContent>
                                </Dialog>
                            </td>
                        </tr>
                        <tr v-if="curso.materias.length === 0">
                            <td
                                colspan="3"
                                class="px-4 py-6 text-center text-muted-foreground"
                            >
                                Todavía no hay materias asignadas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Form
                v-if="materiasDisponibles.length > 0"
                v-bind="CursoMateriaController.store.form(curso.id)"
                :options="{ preserveScroll: true }"
                class="flex items-end gap-2"
                v-slot="{ errors, processing: adding }"
            >
                <div class="grid flex-1 gap-2">
                    <Label for="materia_id">Materia</Label>
                    <select
                        id="materia_id"
                        name="materia_id"
                        required
                        :class="selectClass"
                    >
                        <option
                            v-for="m in materiasDisponibles"
                            :key="m.id"
                            :value="m.id"
                        >
                            {{ m.nombre }}
                        </option>
                    </select>
                    <InputError :message="errors.materia_id" />
                </div>

                <div class="grid flex-1 gap-2">
                    <Label for="profesor_id">Profesor</Label>
                    <select
                        id="profesor_id"
                        name="profesor_id"
                        :class="selectClass"
                    >
                        <option value="">Sin asignar</option>
                        <option
                            v-for="profesor in profesoresActivos"
                            :key="profesor.id"
                            :value="profesor.id"
                        >
                            {{ profesor.apellido }}, {{ profesor.nombre }}
                        </option>
                    </select>
                    <InputError :message="errors.profesor_id" />
                </div>

                <Button :disabled="adding">Agregar</Button>
            </Form>
            <p v-else class="text-sm text-muted-foreground">
                Todas las materias ya están asignadas a este curso.
            </p>
        </div>

        <div class="max-w-5xl space-y-4 border-t pt-6">
            <Heading
                variant="small"
                title="Alumnos matriculados"
                description="Alumnos inscriptos en este curso"
            />

            <div class="overflow-hidden rounded-md border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-left">
                        <tr>
                            <th class="px-4 py-2 font-medium">Alumno</th>
                            <th class="px-4 py-2 font-medium">
                                Fecha matriculación
                            </th>
                            <th class="px-4 py-2 font-medium">Estado</th>
                            <th class="px-4 py-2 font-medium">
                                <span class="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="alumno in curso.alumnos"
                            :key="alumno.id"
                            class="border-t"
                        >
                            <td class="px-4 py-2">
                                {{ alumno.apellido }}, {{ alumno.nombre }}
                            </td>
                            <td class="px-4 py-2">
                                {{
                                    formatDate(alumno.pivot.fecha_matriculacion)
                                }}
                            </td>
                            <td class="px-4 py-2">
                                <select
                                    :value="alumno.pivot.estado"
                                    class="h-8 w-full max-w-xs rounded-md border border-input bg-transparent px-2 text-sm outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                    @change="onEstadoChange(alumno.id, $event)"
                                >
                                    <option value="activo">Activo</option>
                                    <option value="baja">Baja</option>
                                    <option value="egresado">Egresado</option>
                                </select>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <Dialog>
                                    <DialogTrigger as-child>
                                        <Button variant="destructive" size="sm">
                                            Quitar
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <Form
                                            v-bind="
                                                CursoAlumnoController.destroy.form(
                                                    [curso.id, alumno.id],
                                                )
                                            "
                                            :options="{ preserveScroll: true }"
                                            v-slot="{ processing: removing }"
                                        >
                                            <DialogHeader class="space-y-3">
                                                <DialogTitle>
                                                    ¿Quitar la matrícula de
                                                    {{ alumno.nombre }}
                                                    {{ alumno.apellido }} en
                                                    este curso?
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
                                                    :disabled="removing"
                                                >
                                                    Quitar
                                                </Button>
                                            </DialogFooter>
                                        </Form>
                                    </DialogContent>
                                </Dialog>
                            </td>
                        </tr>
                        <tr v-if="curso.alumnos.length === 0">
                            <td
                                colspan="4"
                                class="px-4 py-6 text-center text-muted-foreground"
                            >
                                Todavía no hay alumnos matriculados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Form
                v-if="alumnosDisponibles.length > 0"
                v-bind="CursoAlumnoController.store.form(curso.id)"
                :options="{ preserveScroll: true }"
                class="flex items-end gap-2"
                v-slot="{ errors, processing: matriculando }"
            >
                <div class="grid flex-1 gap-2">
                    <Label for="alumno_id">Alumno</Label>
                    <select
                        id="alumno_id"
                        name="alumno_id"
                        required
                        :class="selectClass"
                    >
                        <option
                            v-for="a in alumnosDisponibles"
                            :key="a.id"
                            :value="a.id"
                        >
                            {{ a.apellido }}, {{ a.nombre }}
                        </option>
                    </select>
                    <InputError :message="errors.alumno_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="fecha_matriculacion">Fecha</Label>
                    <Input
                        id="fecha_matriculacion"
                        type="date"
                        name="fecha_matriculacion"
                        :default-value="today"
                        required
                    />
                    <InputError :message="errors.fecha_matriculacion" />
                </div>

                <Button :disabled="matriculando">Matricular</Button>
            </Form>
            <p v-else class="text-sm text-muted-foreground">
                No hay alumnos activos disponibles para matricular.
            </p>
        </div>
    </div>
</template>
