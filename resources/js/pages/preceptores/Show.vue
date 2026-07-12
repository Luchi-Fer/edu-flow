<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PreceptorController from '@/actions/App/Http/Controllers/PreceptorController';
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
import { useDateFormat } from '@/composables/useDateFormat';
import type { Preceptor } from '@/types';

const props = defineProps<{
    preceptor: Preceptor;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Preceptores', href: PreceptorController.index() },
            { title: 'Ver preceptor', href: PreceptorController.index() },
        ],
    },
});

const { formatDate } = useDateFormat();

const iniciales = `${props.preceptor.nombre[0] ?? ''}${props.preceptor.apellido[0] ?? ''}`;
</script>

<template>
    <Head :title="`${preceptor.nombre} ${preceptor.apellido}`" />

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
                            {{ preceptor.nombre }} {{ preceptor.apellido }}
                        </h2>
                        <Badge
                            :variant="
                                preceptor.activo ? 'default' : 'secondary'
                            "
                        >
                            {{ preceptor.activo ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ preceptor.user.email }}
                    </p>
                </div>
            </div>
            <Button as-child variant="outline">
                <Link :href="PreceptorController.edit(preceptor.id)">
                    Editar
                </Link>
            </Button>
        </div>

        <Card class="max-w-2xl">
            <CardHeader>
                <CardTitle>Datos personales</CardTitle>
                <CardDescription
                    >Información registrada del preceptor</CardDescription
                >
            </CardHeader>
            <CardContent class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-muted-foreground">DNI</p>
                    <p class="font-medium">{{ preceptor.dni }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Teléfono</p>
                    <p class="font-medium">{{ preceptor.telefono ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">Fecha de nacimiento</p>
                    <p class="font-medium">
                        {{ formatDate(preceptor.fecha_nacimiento) }}
                    </p>
                </div>
                <div>
                    <p class="text-muted-foreground">Fecha de ingreso</p>
                    <p class="font-medium">
                        {{ formatDate(preceptor.fecha_ingreso) }}
                    </p>
                </div>
                <div class="col-span-2">
                    <p class="text-muted-foreground">Dirección</p>
                    <p class="font-medium">{{ preceptor.direccion ?? '—' }}</p>
                </div>
            </CardContent>
        </Card>

        <Link
            :href="PreceptorController.index()"
            class="text-sm text-muted-foreground"
        >
            Volver
        </Link>
    </div>
</template>
