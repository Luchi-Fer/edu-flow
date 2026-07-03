<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import CursoController from '@/actions/App/Http/Controllers/CursoController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { CicloLectivo, Curso } from '@/types';

const props = defineProps<{
    curso: Curso;
    ciclosLectivos: CicloLectivo[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Cursos', href: CursoController.index() },
            { title: 'Editar curso', href: CursoController.index() },
        ],
    },
});

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm';
</script>

<template>
    <Head :title="`Editar curso ${curso.anio}° ${curso.division}`" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Editar curso"
            :description="`${curso.anio}° ${curso.division}`"
        />

        <Form
            v-bind="CursoController.update.form(curso.id)"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="ciclo_lectivo_id">Ciclo lectivo</Label>
                <select
                    id="ciclo_lectivo_id"
                    name="ciclo_lectivo_id"
                    required
                    :value="curso.ciclo_lectivo_id"
                    :class="selectClass"
                >
                    <option
                        v-for="ciclo in props.ciclosLectivos"
                        :key="ciclo.id"
                        :value="ciclo.id"
                    >
                        {{ ciclo.anio }}
                    </option>
                </select>
                <InputError :message="errors.ciclo_lectivo_id" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="anio">Año</Label>
                    <select
                        id="anio"
                        name="anio"
                        required
                        :value="curso.anio"
                        :class="selectClass"
                    >
                        <option v-for="n in 7" :key="n" :value="n">
                            {{ n }}°
                        </option>
                    </select>
                    <InputError :message="errors.anio" />
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
            </div>

            <div class="grid gap-2">
                <Label for="turno">Turno</Label>
                <select
                    id="turno"
                    name="turno"
                    :value="curso.turno ?? ''"
                    :class="selectClass"
                >
                    <option value="">Sin especificar</option>
                    <option value="mañana">Mañana</option>
                    <option value="tarde">Tarde</option>
                    <option value="noche">Noche</option>
                </select>
                <InputError :message="errors.turno" />
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
    </div>
</template>
