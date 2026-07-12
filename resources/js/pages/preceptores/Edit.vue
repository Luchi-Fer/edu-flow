<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import PreceptorController from '@/actions/App/Http/Controllers/PreceptorController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useDateFormat } from '@/composables/useDateFormat';
import type { Preceptor } from '@/types';

defineProps<{
    preceptor: Preceptor;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Preceptores', href: PreceptorController.index() },
            { title: 'Editar preceptor', href: PreceptorController.index() },
        ],
    },
});

const { toDateInputValue } = useDateFormat();
</script>

<template>
    <Head :title="`Editar ${preceptor.nombre} ${preceptor.apellido}`" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Editar preceptor"
            :description="`${preceptor.nombre} ${preceptor.apellido}`"
        />

        <Form
            v-bind="PreceptorController.update.form(preceptor.id)"
            class="max-w-5xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid grid-cols-3 gap-4">
                <div class="grid gap-2">
                    <Label for="nombre">Nombre</Label>
                    <Input
                        id="nombre"
                        name="nombre"
                        :default-value="preceptor.nombre"
                        required
                    />
                    <InputError :message="errors.nombre" />
                </div>

                <div class="grid gap-2">
                    <Label for="apellido">Apellido</Label>
                    <Input
                        id="apellido"
                        name="apellido"
                        :default-value="preceptor.apellido"
                        required
                    />
                    <InputError :message="errors.apellido" />
                </div>

                <div class="grid gap-2">
                    <Label for="dni">DNI</Label>
                    <Input
                        id="dni"
                        name="dni"
                        :default-value="preceptor.dni"
                        required
                    />
                    <InputError :message="errors.dni" />
                </div>

                <div class="grid gap-2">
                    <Label for="telefono">Teléfono</Label>
                    <Input
                        id="telefono"
                        name="telefono"
                        :default-value="preceptor.telefono ?? ''"
                    />
                    <InputError :message="errors.telefono" />
                </div>

                <div class="grid gap-2">
                    <Label for="fecha_nacimiento">Fecha de nacimiento</Label>
                    <Input
                        id="fecha_nacimiento"
                        type="date"
                        name="fecha_nacimiento"
                        :default-value="
                            toDateInputValue(preceptor.fecha_nacimiento)
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
                        :default-value="
                            toDateInputValue(preceptor.fecha_ingreso)
                        "
                        required
                    />
                    <InputError :message="errors.fecha_ingreso" />
                </div>

                <div class="col-span-3 grid gap-2">
                    <Label for="direccion">Dirección</Label>
                    <Input
                        id="direccion"
                        name="direccion"
                        :default-value="preceptor.direccion ?? ''"
                    />
                    <InputError :message="errors.direccion" />
                </div>
            </div>

            <div class="space-y-2 border-t pt-6">
                <p class="text-sm text-muted-foreground">
                    Cuenta de acceso:
                    <span class="font-medium text-foreground">{{
                        preceptor.user.email
                    }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="activo" value="0" />
                <Checkbox
                    id="activo"
                    name="activo"
                    value="1"
                    :default-value="preceptor.activo"
                />
                <Label for="activo">Preceptor activo</Label>
                <InputError :message="errors.activo" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Guardar</Button>
                <Link
                    :href="PreceptorController.index()"
                    class="text-sm text-muted-foreground"
                >
                    Cancelar
                </Link>
            </div>
        </Form>
    </div>
</template>
