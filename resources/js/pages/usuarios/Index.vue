<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import UsuarioController from '@/actions/App/Http/Controllers/UsuarioController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import type { Usuario } from '@/types';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    usuarios: {
        data: Usuario[];
        links: PaginationLink[];
    };
    search: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Usuarios', href: UsuarioController.index() }],
    },
});

const page = usePage();
const search = ref(props.search);

function onSearch() {
    router.get(
        UsuarioController.index().url,
        { search: search.value },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <Head title="Usuarios" />

    <div class="flex flex-col space-y-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Usuarios"
                description="Cuentas de acceso al sistema y sus roles"
            />
            <Button as-child>
                <Link :href="UsuarioController.create()">Nuevo usuario</Link>
            </Button>
        </div>

        <form class="flex gap-2" @submit.prevent="onSearch">
            <Input
                v-model="search"
                placeholder="Buscar por nombre o email"
                class="max-w-sm"
            />
            <Button type="submit" variant="secondary">Buscar</Button>
        </form>

        <div class="overflow-hidden rounded-md border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium">Nombre</th>
                        <th class="px-4 py-2 font-medium">Email</th>
                        <th class="px-4 py-2 font-medium">Rol</th>
                        <th class="px-4 py-2 font-medium">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="usuario in usuarios.data"
                        :key="usuario.id"
                        class="border-t"
                    >
                        <td class="px-4 py-2">{{ usuario.name }}</td>
                        <td class="px-4 py-2">{{ usuario.email }}</td>
                        <td class="px-4 py-2">
                            <Badge>{{
                                usuario.roles[0]?.name ?? 'Sin rol'
                            }}</Badge>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-2">
                                <Button as-child variant="outline" size="sm">
                                    <Link
                                        :href="
                                            UsuarioController.edit(usuario.id)
                                        "
                                    >
                                        Editar
                                    </Link>
                                </Button>

                                <Dialog
                                    v-if="
                                        usuario.id !== page.props.auth.user.id
                                    "
                                >
                                    <DialogTrigger as-child>
                                        <Button variant="destructive" size="sm">
                                            Eliminar
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <Form
                                            v-bind="
                                                UsuarioController.destroy.form(
                                                    usuario.id,
                                                )
                                            "
                                            :options="{ preserveScroll: true }"
                                            v-slot="{ processing }"
                                        >
                                            <DialogHeader class="space-y-3">
                                                <DialogTitle>
                                                    ¿Eliminar a
                                                    {{ usuario.name }}?
                                                </DialogTitle>
                                            </DialogHeader>

                                            <DialogFooter class="mt-6 gap-2">
                                                <DialogClose as-child>
                                                    <Button variant="secondary">
                                                        Cancelar
                                                    </Button>
                                                </DialogClose>

                                                <Button
                                                    type="submit"
                                                    variant="destructive"
                                                    :disabled="processing"
                                                >
                                                    Eliminar
                                                </Button>
                                            </DialogFooter>
                                        </Form>
                                    </DialogContent>
                                </Dialog>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="usuarios.data.length === 0">
                        <td
                            colspan="4"
                            class="px-4 py-6 text-center text-muted-foreground"
                        >
                            No hay usuarios que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="usuarios.links.length > 3" class="flex gap-1">
            <template v-for="(link, index) in usuarios.links" :key="index">
                <span
                    v-if="!link.url"
                    class="rounded-md px-3 py-1 text-sm text-muted-foreground"
                    v-html="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    class="rounded-md px-3 py-1 text-sm"
                    :class="
                        link.active
                            ? 'bg-primary text-primary-foreground'
                            : 'hover:bg-muted'
                    "
                >
                    <span v-html="link.label" />
                </Link>
            </template>
        </div>
    </div>
</template>
