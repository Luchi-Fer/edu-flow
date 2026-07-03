<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AlumnoController from '@/actions/App/Http/Controllers/AlumnoController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Alumnos', href: AlumnoController.index() },
            { title: 'Nuevo alumno', href: AlumnoController.create() },
        ],
    },
});
</script>

<template>
    <Head title="Nuevo alumno" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Nuevo alumno"
            description="Cargar los datos del alumno"
        />

        <Form
            v-bind="AlumnoController.store.form()"
            class="max-w-5xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid grid-cols-3 gap-4">
                <div class="grid gap-2">
                    <Label for="nombre">Nombre</Label>
                    <Input id="nombre" name="nombre" required />
                    <InputError :message="errors.nombre" />
                </div>

                <div class="grid gap-2">
                    <Label for="apellido">Apellido</Label>
                    <Input id="apellido" name="apellido" required />
                    <InputError :message="errors.apellido" />
                </div>

                <div class="grid gap-2">
                    <Label for="dni">DNI</Label>
                    <Input id="dni" name="dni" required />
                    <InputError :message="errors.dni" />
                </div>

                <div class="grid gap-2">
                    <Label for="genero">Género</Label>
                    <select
                        id="genero"
                        name="genero"
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
                        required
                    />
                    <InputError :message="errors.fecha_ingreso" />
                </div>

                <div class="col-span-2 grid gap-2">
                    <Label for="direccion">Dirección</Label>
                    <Input id="direccion" name="direccion" />
                    <InputError :message="errors.direccion" />
                </div>

                <div class="grid gap-2">
                    <Label for="telefono">Teléfono</Label>
                    <Input id="telefono" name="telefono" />
                    <InputError :message="errors.telefono" />
                </div>

                <div class="grid gap-2">
                    <Label for="nombre_tutor">Nombre del tutor</Label>
                    <Input id="nombre_tutor" name="nombre_tutor" />
                    <InputError :message="errors.nombre_tutor" />
                </div>

                <div class="grid gap-2">
                    <Label for="telefono_tutor">Teléfono del tutor</Label>
                    <Input id="telefono_tutor" name="telefono_tutor" />
                    <InputError :message="errors.telefono_tutor" />
                </div>

                <div class="grid gap-2">
                    <Label for="email_tutor">Email del tutor</Label>
                    <Input id="email_tutor" type="email" name="email_tutor" />
                    <InputError :message="errors.email_tutor" />
                </div>
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
