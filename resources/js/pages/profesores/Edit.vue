<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ProfesorController from '@/actions/App/Http/Controllers/ProfesorController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Profesor } from '@/types';

defineProps<{
    profesor: Profesor;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Profesores', href: ProfesorController.index() },
            { title: 'Editar profesor', href: ProfesorController.index() },
        ],
    },
});
</script>

<template>
    <Head :title="`Editar ${profesor.nombre} ${profesor.apellido}`" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Editar profesor"
            :description="`${profesor.nombre} ${profesor.apellido}`"
        />

        <Form
            v-bind="ProfesorController.update.form(profesor.id)"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="nombre">Nombre</Label>
                    <Input
                        id="nombre"
                        name="nombre"
                        :default-value="profesor.nombre"
                        required
                    />
                    <InputError :message="errors.nombre" />
                </div>

                <div class="grid gap-2">
                    <Label for="apellido">Apellido</Label>
                    <Input
                        id="apellido"
                        name="apellido"
                        :default-value="profesor.apellido"
                        required
                    />
                    <InputError :message="errors.apellido" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="dni">DNI</Label>
                <Input
                    id="dni"
                    name="dni"
                    :default-value="profesor.dni"
                    required
                />
                <InputError :message="errors.dni" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="fecha_nacimiento">Fecha de nacimiento</Label>
                    <Input
                        id="fecha_nacimiento"
                        type="date"
                        name="fecha_nacimiento"
                        :default-value="profesor.fecha_nacimiento"
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
                        :default-value="profesor.fecha_ingreso"
                        required
                    />
                    <InputError :message="errors.fecha_ingreso" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="direccion">Dirección</Label>
                <Input
                    id="direccion"
                    name="direccion"
                    :default-value="profesor.direccion ?? ''"
                />
                <InputError :message="errors.direccion" />
            </div>

            <div class="grid gap-2">
                <Label for="telefono">Teléfono</Label>
                <Input
                    id="telefono"
                    name="telefono"
                    :default-value="profesor.telefono ?? ''"
                />
                <InputError :message="errors.telefono" />
            </div>

            <div class="space-y-2 border-t pt-6">
                <p class="text-sm text-muted-foreground">
                    Cuenta de acceso:
                    <span class="font-medium text-foreground">{{
                        profesor.user.email
                    }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="activo" value="0" />
                <Checkbox
                    id="activo"
                    name="activo"
                    value="1"
                    :default-checked="profesor.activo"
                />
                <Label for="activo">Profesor activo</Label>
                <InputError :message="errors.activo" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Guardar</Button>
                <Link
                    :href="ProfesorController.index()"
                    class="text-sm text-muted-foreground"
                >
                    Cancelar
                </Link>
            </div>
        </Form>
    </div>
</template>
