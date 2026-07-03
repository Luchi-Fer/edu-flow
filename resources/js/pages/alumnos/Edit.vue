<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AlumnoController from '@/actions/App/Http/Controllers/AlumnoController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useDateFormat } from '@/composables/useDateFormat';
import type { Alumno } from '@/types';

defineProps<{
    alumno: Alumno;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Alumnos', href: AlumnoController.index() },
            { title: 'Editar alumno', href: AlumnoController.index() },
        ],
    },
});

const { toDateInputValue } = useDateFormat();
</script>

<template>
    <Head :title="`Editar ${alumno.nombre} ${alumno.apellido}`" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Editar alumno"
            :description="`${alumno.nombre} ${alumno.apellido}`"
        />

        <Form
            v-bind="AlumnoController.update.form(alumno.id)"
            class="max-w-5xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid grid-cols-3 gap-4">
                <div class="grid gap-2">
                    <Label for="nombre">Nombre</Label>
                    <Input
                        id="nombre"
                        name="nombre"
                        :default-value="alumno.nombre"
                        required
                    />
                    <InputError :message="errors.nombre" />
                </div>

                <div class="grid gap-2">
                    <Label for="apellido">Apellido</Label>
                    <Input
                        id="apellido"
                        name="apellido"
                        :default-value="alumno.apellido"
                        required
                    />
                    <InputError :message="errors.apellido" />
                </div>

                <div class="grid gap-2">
                    <Label for="dni">DNI</Label>
                    <Input
                        id="dni"
                        name="dni"
                        :default-value="alumno.dni"
                        required
                    />
                    <InputError :message="errors.dni" />
                </div>

                <div class="grid gap-2">
                    <Label for="genero">Género</Label>
                    <select
                        id="genero"
                        name="genero"
                        :value="alumno.genero ?? ''"
                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm"
                    >
                        <option value="">Sin especificar</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                        <option value="X">Otro</option>
                    </select>
                    <InputError :message="errors.genero" />
                </div>

                <div class="grid gap-2">
                    <Label for="fecha_nacimiento">Fecha de nacimiento</Label>
                    <Input
                        id="fecha_nacimiento"
                        type="date"
                        name="fecha_nacimiento"
                        :default-value="
                            toDateInputValue(alumno.fecha_nacimiento)
                        "
                        required
                    />
                    <InputError :message="errors.fecha_nacimiento" />
                </div>

                <div class="grid gap-2">
                    <Label for="fecha_ingreso">Fecha de ingreso</Label>
                    <Input
                        id="fecha_ingreso"
                        type="date"
                        name="fecha_ingreso"
                        :default-value="toDateInputValue(alumno.fecha_ingreso)"
                        required
                    />
                    <InputError :message="errors.fecha_ingreso" />
                </div>

                <div class="col-span-2 grid gap-2">
                    <Label for="direccion">Dirección</Label>
                    <Input
                        id="direccion"
                        name="direccion"
                        :default-value="alumno.direccion ?? ''"
                    />
                    <InputError :message="errors.direccion" />
                </div>

                <div class="grid gap-2">
                    <Label for="telefono">Teléfono</Label>
                    <Input
                        id="telefono"
                        name="telefono"
                        :default-value="alumno.telefono ?? ''"
                    />
                    <InputError :message="errors.telefono" />
                </div>

                <div class="grid gap-2">
                    <Label for="nombre_tutor">Nombre del tutor</Label>
                    <Input
                        id="nombre_tutor"
                        name="nombre_tutor"
                        :default-value="alumno.nombre_tutor ?? ''"
                    />
                    <InputError :message="errors.nombre_tutor" />
                </div>

                <div class="grid gap-2">
                    <Label for="telefono_tutor">Teléfono del tutor</Label>
                    <Input
                        id="telefono_tutor"
                        name="telefono_tutor"
                        :default-value="alumno.telefono_tutor ?? ''"
                    />
                    <InputError :message="errors.telefono_tutor" />
                </div>

                <div class="grid gap-2">
                    <Label for="email_tutor">Email del tutor</Label>
                    <Input
                        id="email_tutor"
                        type="email"
                        name="email_tutor"
                        :default-value="alumno.email_tutor ?? ''"
                    />
                    <InputError :message="errors.email_tutor" />
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="activo" value="0" />
                <Checkbox
                    id="activo"
                    name="activo"
                    value="1"
                    :default-value="alumno.activo"
                />
                <Label for="activo">Alumno activo</Label>
                <InputError :message="errors.activo" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Guardar</Button>
                <Link
                    :href="AlumnoController.index()"
                    class="text-sm text-muted-foreground"
                >
                    Cancelar
                </Link>
            </div>
        </Form>
    </div>
</template>
