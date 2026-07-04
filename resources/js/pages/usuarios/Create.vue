<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import UsuarioController from '@/actions/App/Http/Controllers/UsuarioController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    roles: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Usuarios', href: UsuarioController.index() },
            { title: 'Nuevo usuario', href: UsuarioController.create() },
        ],
    },
});

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm';
</script>

<template>
    <Head title="Nuevo usuario" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            title="Nuevo usuario"
            description="Crear una cuenta de acceso y asignarle un rol"
        />

        <Form
            v-bind="UsuarioController.store.form()"
            class="max-w-5xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid grid-cols-3 gap-4">
                <div class="grid gap-2">
                    <Label for="name">Nombre</Label>
                    <Input id="name" name="name" required />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" name="email" required />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="role">Rol</Label>
                    <select id="role" name="role" required :class="selectClass">
                        <option value="" disabled selected>
                            Seleccionar...
                        </option>
                        <option v-for="role in roles" :key="role" :value="role">
                            {{ role }}
                        </option>
                    </select>
                    <InputError :message="errors.role" />
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
