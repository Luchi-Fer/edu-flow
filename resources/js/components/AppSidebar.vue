<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    CalendarRange,
    GraduationCap,
    Layers,
    LayoutGrid,
    Library,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import AlumnoController from '@/actions/App/Http/Controllers/AlumnoController';
import CicloLectivoController from '@/actions/App/Http/Controllers/CicloLectivoController';
import CursoController from '@/actions/App/Http/Controllers/CursoController';
import MateriaController from '@/actions/App/Http/Controllers/MateriaController';
import ProfesorController from '@/actions/App/Http/Controllers/ProfesorController';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    ...(page.props.can['gestionar-alumnos']
        ? [
              {
                  title: 'Alumnos',
                  href: AlumnoController.index(),
                  icon: GraduationCap,
              },
          ]
        : []),
    ...(page.props.can['gestionar-profesores']
        ? [
              {
                  title: 'Profesores',
                  href: ProfesorController.index(),
                  icon: Users,
              },
          ]
        : []),
    ...(page.props.can['gestionar-materias']
        ? [
              {
                  title: 'Materias',
                  href: MateriaController.index(),
                  icon: Library,
              },
          ]
        : []),
    ...(page.props.can['gestionar-cursos'] || page.props.can['tomar-asistencia']
        ? [
              {
                  title: 'Cursos',
                  href: CursoController.index(),
                  icon: Layers,
              },
          ]
        : []),
    ...(page.props.can['gestionar-cursos']
        ? [
              {
                  title: 'Ciclos Lectivos',
                  href: CicloLectivoController.index(),
                  icon: CalendarRange,
              },
          ]
        : []),
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
