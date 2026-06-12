<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Save, X } from 'lucide-vue-next';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { roleLabel } from '@/constants/adminUi';
import { dashboard } from '@/routes/admin';
import { index as indexRoute } from '@/routes/admin/users';

type RoleOpt = { name: string };

type UserPayload = {
    id: number;
    name: string;
    email: string;
    roles: string[];
};

type Props = {
    user: UserPayload;
    availableRoles: RoleOpt[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Painel', href: dashboard() },
            { title: 'Usuários', href: indexRoute() },
        ],
    },
});

function roleChecked(user: UserPayload, roleName: string): boolean {
    return user.roles.includes(roleName);
}
</script>

<template>
    <Head :title="`Editar ${user.name}`" />

    <div class="w-full space-y-8">
        <Heading
            title="Editar usuário"
            description="Atualize dados, senha e perfis"
        />

        <Form
            v-bind="UserController.update.form(user.id)"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Nome</Label>
                <Input
                    id="name"
                    name="name"
                    required
                    autocomplete="name"
                    :default-value="user.name"
                    placeholder="Nome completo"
                />
                <InputError class="mt-1" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">E-mail</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autocomplete="username"
                    :default-value="user.email"
                    placeholder="email@exemplo.com"
                />
                <InputError class="mt-1" :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Nova senha</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    placeholder="Deixe em branco para manter"
                />
                <InputError class="mt-1" :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirmar nova senha</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    placeholder="Confirme a nova senha"
                />
                <InputError
                    class="mt-1"
                    :message="errors.password_confirmation"
                />
            </div>

            <fieldset class="space-y-3">
                <legend class="text-sm font-medium">Perfis</legend>
                <div class="space-y-2">
                    <div
                        v-for="role in availableRoles"
                        :key="role.name"
                        class="flex items-center gap-2"
                    >
                        <input
                            :id="`role-${role.name}`"
                            type="checkbox"
                            name="roles[]"
                            :value="role.name"
                            :checked="roleChecked(user, role.name)"
                            class="size-4 rounded border-input"
                        />
                        <Label
                            :for="`role-${role.name}`"
                            class="text-sm font-normal"
                            >{{ roleLabel(role.name) }}</Label
                        >
                    </div>
                </div>
                <InputError class="mt-1" :message="errors.roles" />
            </fieldset>

            <div class="flex gap-3">
                <Button type="submit" :disabled="processing">
                    <Save class="size-4" />
                    Salvar
                </Button>
                <Button variant="outline" type="button" as-child>
                    <Link
                        :href="indexRoute()"
                        class="inline-flex items-center gap-2"
                    >
                        <X class="size-4" />
                        Cancelar
                    </Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
