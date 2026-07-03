<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ProfesorController from '@/actions/App/Http/Controllers/ProfesorController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Profesores', href: ProfesorController.index() },
            { title: 'Nuevo profesor', href: ProfesorController.create() },
        ],
    },
});
</script>

<template>
    <Head title="Nuevo profesor" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Nuevo profesor"
            description="Cargar los datos del profesor y su cuenta de acceso"
        />

        <Form
            v-bind="ProfesorController.store.form()"
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
                    <Label for="telefono">Teléfono</Label>
                    <Input id="telefono" name="telefono" />
                    <InputError :message="errors.telefono" />
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

                <div class="col-span-3 grid gap-2">
                    <Label for="direccion">Dirección</Label>
                    <Input id="direccion" name="direccion" />
                    <InputError :message="errors.direccion" />
                </div>
            </div>

            <div class="space-y-6 border-t pt-6">
                <p class="text-sm text-muted-foreground">
                    Datos de acceso al sistema
                </p>

                <div class="grid grid-cols-3 gap-4">
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" name="email" required />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Contraseña</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            required
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">
                            Confirmar contraseña
                        </Label>
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                            required
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>
                </div>
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
