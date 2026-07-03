<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import CicloLectivoController from '@/actions/App/Http/Controllers/CicloLectivoController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { CicloLectivo } from '@/types';

defineProps<{
    cicloLectivo: CicloLectivo;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Ciclos Lectivos',
                href: CicloLectivoController.index(),
            },
            {
                title: 'Editar ciclo lectivo',
                href: CicloLectivoController.index(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Editar ciclo lectivo ${cicloLectivo.anio}`" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Editar ciclo lectivo"
            :description="String(cicloLectivo.anio)"
        />

        <Form
            v-bind="CicloLectivoController.update.form(cicloLectivo.id)"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="anio">Año</Label>
                <Input
                    id="anio"
                    type="number"
                    name="anio"
                    :default-value="cicloLectivo.anio"
                    required
                />
                <InputError :message="errors.anio" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="fecha_inicio">Fecha de inicio</Label>
                    <Input
                        id="fecha_inicio"
                        type="date"
                        name="fecha_inicio"
                        :default-value="cicloLectivo.fecha_inicio"
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
                        :default-value="cicloLectivo.fecha_fin"
                        required
                    />
                    <InputError :message="errors.fecha_fin" />
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="activo" value="0" />
                <Checkbox
                    id="activo"
                    name="activo"
                    value="1"
                    :default-checked="cicloLectivo.activo"
                />
                <Label for="activo">Ciclo lectivo activo</Label>
                <InputError :message="errors.activo" />
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
