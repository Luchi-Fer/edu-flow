<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AlumnoController from '@/actions/App/Http/Controllers/AlumnoController';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { useDateFormat } from '@/composables/useDateFormat';
import type { Alumno } from '@/types';

type HistorialItem = {
    id: number;
    ciclo_lectivo: number;
    curso: string;
    estado: 'activo' | 'baja' | 'egresado';
    fecha_matriculacion: string;
    asistencia: {
        total: number;
        presentes: number;
        porcentaje: number | null;
    };
};

const props = defineProps<{
    alumno: Alumno;
    historial: HistorialItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Alumnos', href: AlumnoController.index() },
            { title: 'Ver alumno', href: AlumnoController.index() },
        ],
    },
});

const { formatDate } = useDateFormat();

const iniciales = `${props.alumno.nombre[0] ?? ''}${props.alumno.apellido[0] ?? ''}`;

const ESTADO_VARIANT: Record<
    HistorialItem['estado'],
    'default' | 'secondary' | 'destructive'
> = {
    activo: 'default',
    egresado: 'secondary',
    baja: 'destructive',
};

const ESTADO_LABEL: Record<HistorialItem['estado'], string> = {
    activo: 'Activo',
    egresado: 'Egresado',
    baja: 'Baja',
};
</script>

<template>
    <Head :title="`${alumno.nombre} ${alumno.apellido}`" />

    <div class="flex flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Avatar class="size-14">
                    <AvatarFallback class="text-lg">
                        {{ iniciales }}
                    </AvatarFallback>
                </Avatar>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-semibold tracking-tight">
                            {{ alumno.nombre }} {{ alumno.apellido }}
                        </h2>
                        <Badge
                            :variant="alumno.activo ? 'default' : 'secondary'"
                        >
                            {{ alumno.activo ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        DNI {{ alumno.dni }}
                    </p>
                </div>
            </div>
            <Button as-child variant="outline">
                <Link :href="AlumnoController.edit(alumno.id)">Editar</Link>
            </Button>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Datos personales</CardTitle>
                    <CardDescription
                        >Información registrada del alumno</CardDescription
                    >
                </CardHeader>
                <CardContent class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-muted-foreground">Fecha de nacimiento</p>
                        <p class="font-medium">
                            {{ formatDate(alumno.fecha_nacimiento) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Género</p>
                        <p class="font-medium">{{ alumno.genero ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Teléfono</p>
                        <p class="font-medium">{{ alumno.telefono ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Fecha de ingreso</p>
                        <p class="font-medium">
                            {{ formatDate(alumno.fecha_ingreso) }}
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-muted-foreground">Dirección</p>
                        <p class="font-medium">{{ alumno.direccion ?? '—' }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Datos del tutor</CardTitle>
                    <CardDescription
                        >Contacto responsable del alumno</CardDescription
                    >
                </CardHeader>
                <CardContent class="grid grid-cols-2 gap-4 text-sm">
                    <div class="col-span-2">
                        <p class="text-muted-foreground">Nombre</p>
                        <p class="font-medium">
                            {{ alumno.nombre_tutor ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Teléfono</p>
                        <p class="font-medium">
                            {{ alumno.telefono_tutor ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Email</p>
                        <p class="font-medium">
                            {{ alumno.email_tutor ?? '—' }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Historial de cursos</CardTitle>
                <CardDescription>
                    Matriculaciones por ciclo lectivo, con su asistencia
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <template v-for="(item, index) in historial" :key="item.id">
                    <div
                        class="flex flex-wrap items-center justify-between gap-4"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium">
                                    {{ item.ciclo_lectivo }} — {{ item.curso }}
                                </span>
                                <Badge :variant="ESTADO_VARIANT[item.estado]">
                                    {{ ESTADO_LABEL[item.estado] }}
                                </Badge>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Matriculado el
                                {{ formatDate(item.fecha_matriculacion) }}
                            </p>
                        </div>

                        <div
                            v-if="item.asistencia.porcentaje !== null"
                            class="w-full max-w-xs flex-1 sm:w-48"
                        >
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="text-muted-foreground">
                                    Asistencia
                                </span>
                                <span class="font-medium">
                                    {{ item.asistencia.porcentaje }}%
                                </span>
                            </div>
                            <div
                                class="h-1.5 w-full overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    class="h-full rounded-full bg-primary transition-all duration-500"
                                    :style="{
                                        width: item.asistencia.porcentaje + '%',
                                    }"
                                />
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ item.asistencia.presentes }}/{{
                                    item.asistencia.total
                                }}
                                presente
                            </p>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            Sin asistencias registradas.
                        </p>
                    </div>
                    <Separator v-if="index < historial.length - 1" />
                </template>

                <p
                    v-if="historial.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    Este alumno todavía no fue matriculado en ningún curso.
                </p>
            </CardContent>
        </Card>

        <Link
            :href="AlumnoController.index()"
            class="text-sm text-muted-foreground"
        >
            Volver
        </Link>
    </div>
</template>
