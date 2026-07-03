<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import MateriaController from '@/actions/App/Http/Controllers/MateriaController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Materias', href: MateriaController.index() },
            { title: 'Nueva materia', href: MateriaController.create() },
        ],
    },
});
</script>

<template>
    <Head title="Nueva materia" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Nueva materia"
            description="Cargar los datos de la materia"
        />

        <Form
            v-bind="MateriaController.store.form()"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="nombre">Nombre</Label>
                <Input id="nombre" name="nombre" required />
                <InputError :message="errors.nombre" />
            </div>

            <div class="grid gap-2">
                <Label for="descripcion">Descripción</Label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="4"
                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm"
                />
                <InputError :message="errors.descripcion" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Guardar</Button>
                <Link
                    :href="MateriaController.index()"
                    class="text-sm text-muted-foreground"
                >
                    Cancelar
                </Link>
            </div>
        </Form>
    </div>
</template>
