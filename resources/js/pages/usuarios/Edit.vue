<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import UsuarioController from '@/actions/App/Http/Controllers/UsuarioController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Usuario } from '@/types';

const props = defineProps<{
    usuario: Usuario;
    roles: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Usuarios', href: UsuarioController.index() },
            { title: 'Editar usuario', href: UsuarioController.index() },
        ],
    },
});

const page = usePage();
const esUnoMismo = computed(() => page.props.auth.user.id === props.usuario.id);

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm disabled:cursor-not-allowed disabled:opacity-50';
</script>

<template>
    <Head :title="`Editar ${usuario.name}`" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading title="Editar usuario" :description="usuario.name" />

        <Form
            v-bind="UsuarioController.update.form(usuario.id)"
            class="max-w-5xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid grid-cols-3 gap-4">
                <div class="grid gap-2">
                    <Label for="name">Nombre</Label>
                    <Input
                        id="name"
                        name="name"
                        :default-value="usuario.name"
                        required
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        :default-value="usuario.email"
                        required
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="role">Rol</Label>
                    <input
                        v-if="esUnoMismo"
                        type="hidden"
                        name="role"
                        :value="usuario.roles[0]?.name"
                    />
                    <select
                        id="role"
                        name="role"
                        required
                        :disabled="esUnoMismo"
                        :class="selectClass"
                    >
                        <option
                            v-for="role in roles"
                            :key="role"
                            :value="role"
                            :selected="role === usuario.roles[0]?.name"
                        >
                            {{ role }}
                        </option>
                    </select>
                    <p v-if="esUnoMismo" class="text-sm text-muted-foreground">
                        No podés cambiar tu propio rol.
                    </p>
                    <InputError :message="errors.role" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Nueva contraseña</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        placeholder="Dejar en blanco para no cambiarla"
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
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Guardar</Button>
                <Link
                    :href="UsuarioController.index()"
                    class="text-sm text-muted-foreground"
                >
                    Cancelar
                </Link>
            </div>
        </Form>
    </div>
</template>
