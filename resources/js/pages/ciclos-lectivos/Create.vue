<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import CicloLectivoController from '@/actions/App/Http/Controllers/CicloLectivoController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Ciclos Lectivos',
                href: CicloLectivoController.index(),
            },
            {
                title: 'Nuevo ciclo lectivo',
                href: CicloLectivoController.create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Nuevo ciclo lectivo" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Nuevo ciclo lectivo"
            description="Cargar un nuevo año lectivo"
        />

        <Form
            v-bind="CicloLectivoController.store.form()"
            class="max-w-5xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid grid-cols-3 gap-4">
                <div class="grid gap-2">
                    <Label for="anio">Año</Label>
                    <Input id="anio" type="number" name="anio" required />
                    <InputError :message="errors.anio" />
                </div>

                <div class="grid gap-2">
                    <Label for="fecha_inicio">Fecha de inicio</Label>
                    <Input
                        id="fecha_inicio"
                        type="date"
                        name="fecha_inicio"
                        required
                    />
                    <InputError :message="errors.fecha_inicio" />
                </div>

                <div class="grid gap-2">
                    <Label for="fecha_fin">Fecha de fin</Label>
                    <Input
                        id="fecha_fin"
                        type="date"
                        name="fecha_fin"
                        required
                    />
                    <InputError :message="errors.fecha_fin" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Guardar</Button>
                <Link
                    :href="CicloLectivoController.index()"
                    class="text-sm text-muted-foreground"
                >
                    Cancelar
                </Link>
            </div>
        </Form>
    </div>
</template>
