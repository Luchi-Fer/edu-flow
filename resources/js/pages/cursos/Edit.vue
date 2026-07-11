<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { X } from '@lucide/vue';
import { computed, ref } from 'vue';
import CursoAlumnoController from '@/actions/App/Http/Controllers/CursoAlumnoController';
import CursoController from '@/actions/App/Http/Controllers/CursoController';
import CursoMateriaController from '@/actions/App/Http/Controllers/CursoMateriaController';
import HorarioController from '@/actions/App/Http/Controllers/HorarioController';
import ProfesorController from '@/actions/App/Http/Controllers/ProfesorController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { SearchCombobox } from '@/components/ui/combobox';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useDateFormat } from '@/composables/useDateFormat';
import { DIA_SEMANA_ABREVIADO, DIAS_SEMANA } from '@/lib/diaSemana';
import type { Alumno, CicloLectivo, Curso, Materia, Profesor } from '@/types';

type HorarioSlot = {
    id: number;
    dia_semana: string;
    hora_inicio: string;
    hora_fin: string;
};

type MateriaAsignada = Pick<Materia, 'id' | 'nombre'> & {
    pivot: { profesor_id: number | null; horarios: HorarioSlot[] };
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
    profesoresAsignados: ProfesorOption[];
    etiquetasAnioPorNivel: Record<'primaria' | 'secundaria', string[]>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Cursos', href: CursoController.index() },
            { title: 'Editar curso', href: CursoController.index() },
        ],
    },
});

const TURNO_SIN_ESPECIFICAR = 'sin_especificar';

function labelProfesorAsignado(profesorId: number | null): string | null {
    const profesor = props.profesoresAsignados.find((p) => p.id === profesorId);

    return profesor ? `${profesor.apellido}, ${profesor.nombre}` : null;
}

function onProfesorChange(materiaId: number, profesorId: number | null) {
    router.patch(
        CursoMateriaController.update.url([props.curso.id, materiaId]),
        { profesor_id: profesorId },
        { preserveScroll: true },
    );
}

function eliminarHorario(materiaId: number, horarioId: number) {
    router.delete(
        HorarioController.destroy.url([props.curso.id, materiaId, horarioId]),
        { preserveScroll: true },
    );
}

function onEstadoChange(alumnoId: number, value: string) {
    router.patch(
        CursoAlumnoController.update.url([props.curso.id, alumnoId]),
        { estado: value },
        { preserveScroll: true },
    );
}

const today = new Date().toISOString().slice(0, 10);

const { formatDate } = useDateFormat();

const cicloLectivoId = ref(props.curso.ciclo_lectivo_id.toString());
const nivel = ref<'primaria' | 'secundaria'>(props.curso.nivel);
const anioGradoValue = ref(props.curso.anio_grado.toString());
const turnoValue = ref(props.curso.turno ?? TURNO_SIN_ESPECIFICAR);
const etiquetasAnioGrado = computed(
    () => props.etiquetasAnioPorNivel[nivel.value],
);

const diaSemanaSeleccionado = ref<string>(DIAS_SEMANA[0].value);

const materiaSeleccionadaManual = ref<string | null>(null);
const materiaSeleccionada = computed({
    get: () =>
        materiaSeleccionadaManual.value ??
        props.materiasDisponibles[0]?.id.toString() ??
        '',
    set: (value: string) => {
        materiaSeleccionadaManual.value = value;
    },
});

const profesorParaAgregar = ref<number | null>(null);
const alumnoParaMatricular = ref<number | null>(null);
const profesorBuscarUrl = ProfesorController.buscar().url;
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
                    <Select v-model="cicloLectivoId" name="ciclo_lectivo_id" required>
                        <SelectTrigger id="ciclo_lectivo_id" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="ciclo in props.ciclosLectivos"
                                :key="ciclo.id"
                                :value="ciclo.id.toString()"
                            >
                                {{ ciclo.anio }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.ciclo_lectivo_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="nivel">Nivel</Label>
                    <Select v-model="nivel" name="nivel" required>
                        <SelectTrigger id="nivel" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="primaria">Primaria</SelectItem>
                            <SelectItem value="secundaria">
                                Secundaria
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.nivel" />
                </div>

                <div class="grid gap-2">
                    <Label for="anio_grado">Año</Label>
                    <Select v-model="anioGradoValue" name="anio_grado" required>
                        <SelectTrigger id="anio_grado" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="(etiqueta, indice) in etiquetasAnioGrado"
                                :key="etiqueta"
                                :value="(indice + 1).toString()"
                            >
                                {{ etiqueta }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.anio_grado" />
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
                    <Select v-model="turnoValue">
                        <SelectTrigger id="turno" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="TURNO_SIN_ESPECIFICAR">
                                Sin especificar
                            </SelectItem>
                            <SelectItem value="mañana">Mañana</SelectItem>
                            <SelectItem value="tarde">Tarde</SelectItem>
                            <SelectItem value="noche">Noche</SelectItem>
                        </SelectContent>
                    </Select>
                    <input
                        type="hidden"
                        name="turno"
                        :value="
                            turnoValue === TURNO_SIN_ESPECIFICAR
                                ? ''
                                : turnoValue
                        "
                    />
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
                            <th class="px-4 py-2 font-medium">Horarios</th>
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
                            <td class="max-w-xs px-4 py-2">
                                <SearchCombobox
                                    :model-value="materia.pivot.profesor_id"
                                    :search-url="profesorBuscarUrl"
                                    :initial-label="
                                        labelProfesorAsignado(
                                            materia.pivot.profesor_id,
                                        )
                                    "
                                    allow-clear
                                    placeholder="Sin asignar"
                                    empty-text="No hay profesores activos."
                                    @update:model-value="
                                        (value) =>
                                            onProfesorChange(materia.id, value)
                                    "
                                />
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex flex-wrap items-center gap-1">
                                    <span
                                        v-for="horario in materia.pivot
                                            .horarios"
                                        :key="horario.id"
                                        class="inline-flex items-center gap-1 rounded-md border bg-muted/50 px-2 py-0.5 text-xs whitespace-nowrap"
                                    >
                                        {{
                                            DIA_SEMANA_ABREVIADO[
                                                horario.dia_semana
                                            ]
                                        }}
                                        {{ horario.hora_inicio.slice(0, 5) }}–{{
                                            horario.hora_fin.slice(0, 5)
                                        }}
                                        <button
                                            type="button"
                                            class="text-muted-foreground hover:text-destructive"
                                            :aria-label="`Quitar horario de ${materia.nombre}`"
                                            @click="
                                                eliminarHorario(
                                                    materia.id,
                                                    horario.id,
                                                )
                                            "
                                        >
                                            <X class="size-3" />
                                        </button>
                                    </span>

                                    <Dialog>
                                        <DialogTrigger as-child>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                class="h-6 px-2 text-xs"
                                            >
                                                + Horario
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <Form
                                                v-bind="
                                                    HorarioController.store.form(
                                                        [curso.id, materia.id],
                                                    )
                                                "
                                                :options="{
                                                    preserveScroll: true,
                                                }"
                                                v-slot="{
                                                    errors,
                                                    processing:
                                                        agregandoHorario,
                                                }"
                                            >
                                                <DialogHeader class="space-y-3">
                                                    <DialogTitle>
                                                        Agregar horario —
                                                        {{ materia.nombre }}
                                                    </DialogTitle>
                                                </DialogHeader>

                                                <div
                                                    class="grid grid-cols-3 gap-2 py-4"
                                                >
                                                    <div class="grid gap-2">
                                                        <Label for="dia_semana">
                                                            Día
                                                        </Label>
                                                        <Select
                                                            v-model="
                                                                diaSemanaSeleccionado
                                                            "
                                                            name="dia_semana"
                                                            required
                                                        >
                                                            <SelectTrigger
                                                                id="dia_semana"
                                                                class="w-full"
                                                            >
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem
                                                                    v-for="dia in DIAS_SEMANA"
                                                                    :key="
                                                                        dia.value
                                                                    "
                                                                    :value="
                                                                        dia.value
                                                                    "
                                                                >
                                                                    {{
                                                                        dia.label
                                                                    }}
                                                                </SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                        <InputError
                                                            :message="
                                                                errors.dia_semana
                                                            "
                                                        />
                                                    </div>

                                                    <div class="grid gap-2">
                                                        <Label
                                                            for="hora_inicio"
                                                        >
                                                            Desde
                                                        </Label>
                                                        <Input
                                                            id="hora_inicio"
                                                            type="time"
                                                            name="hora_inicio"
                                                            required
                                                        />
                                                        <InputError
                                                            :message="
                                                                errors.hora_inicio
                                                            "
                                                        />
                                                    </div>

                                                    <div class="grid gap-2">
                                                        <Label for="hora_fin">
                                                            Hasta
                                                        </Label>
                                                        <Input
                                                            id="hora_fin"
                                                            type="time"
                                                            name="hora_fin"
                                                            required
                                                        />
                                                        <InputError
                                                            :message="
                                                                errors.hora_fin
                                                            "
                                                        />
                                                    </div>
                                                </div>

                                                <DialogFooter class="gap-2">
                                                    <DialogClose as-child>
                                                        <Button
                                                            variant="secondary"
                                                        >
                                                            Cancelar
                                                        </Button>
                                                    </DialogClose>

                                                    <Button
                                                        type="submit"
                                                        :disabled="
                                                            agregandoHorario
                                                        "
                                                    >
                                                        Agregar
                                                    </Button>
                                                </DialogFooter>
                                            </Form>
                                        </DialogContent>
                                    </Dialog>
                                </div>
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
                                colspan="4"
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
                @success="
                    profesorParaAgregar = null;
                    materiaSeleccionadaManual = null;
                "
            >
                <div class="grid flex-1 gap-2">
                    <Label for="materia_id">Materia</Label>
                    <Select v-model="materiaSeleccionada" name="materia_id" required>
                        <SelectTrigger id="materia_id" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="m in materiasDisponibles"
                                :key="m.id"
                                :value="m.id.toString()"
                            >
                                {{ m.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.materia_id" />
                </div>

                <div class="grid flex-1 gap-2">
                    <Label for="profesor_id">Profesor</Label>
                    <SearchCombobox
                        v-model="profesorParaAgregar"
                        name="profesor_id"
                        :search-url="profesorBuscarUrl"
                        allow-clear
                        placeholder="Sin asignar"
                        empty-text="No hay profesores activos."
                    />
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
                                <Select
                                    :model-value="alumno.pivot.estado"
                                    @update:model-value="
                                        (value) =>
                                            onEstadoChange(
                                                alumno.id,
                                                value as string,
                                            )
                                    "
                                >
                                    <SelectTrigger size="sm" class="w-full max-w-xs">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="activo">
                                            Activo
                                        </SelectItem>
                                        <SelectItem value="baja">
                                            Baja
                                        </SelectItem>
                                        <SelectItem value="egresado">
                                            Egresado
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
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
                v-bind="CursoAlumnoController.store.form(curso.id)"
                :options="{ preserveScroll: true }"
                class="flex items-end gap-2"
                v-slot="{ errors, processing: matriculando }"
                @success="alumnoParaMatricular = null"
            >
                <div class="grid flex-1 gap-2">
                    <Label for="alumno_id">Alumno</Label>
                    <SearchCombobox
                        v-model="alumnoParaMatricular"
                        name="alumno_id"
                        :search-url="
                            CursoAlumnoController.disponibles.url(curso.id)
                        "
                        placeholder="Buscar alumno..."
                        empty-text="No hay alumnos disponibles para matricular."
                    />
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
        </div>
    </div>
</template>
