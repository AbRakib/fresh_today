<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import {
    CollapsibleContent,
    CollapsibleRoot,
    CollapsibleTrigger,
} from 'reka-ui';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavGroup } from '@/types';

defineProps<{
    groups: NavGroup[];
}>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup
        v-for="group in groups"
        :key="group.title"
        class="px-2 py-0"
    >
        <SidebarGroupLabel v-if="!group.collapsible || group.label">
            {{ group.label ?? group.title }}
        </SidebarGroupLabel>
        <SidebarMenu v-if="group.collapsible">
            <CollapsibleRoot
                class="group/collapsible"
                :default-open="group.items.some((item) => isCurrentUrl(item.href))"
            >
                <SidebarMenuItem>
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton
                            :is-active="group.items.some((item) => isCurrentUrl(item.href))"
                            :tooltip="group.title"
                        >
                            <component :is="group.icon" />
                            <span>{{ group.title }}</span>
                            <ChevronRight
                                class="ml-auto transition-transform group-data-[state=open]/collapsible:rotate-90"
                            />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent
                        class="overflow-hidden group-data-[collapsible=icon]:hidden"
                    >
                        <ul class="ml-4 border-l border-sidebar-border py-1 pl-2">
                            <li v-for="item in group.items" :key="item.title">
                                <SidebarMenuButton
                                    as-child
                                    size="sm"
                                    :is-active="isCurrentUrl(item.href)"
                                >
                                    <Link :href="item.href">
                                        <component :is="item.icon" />
                                        <span>{{ item.title }}</span>
                                    </Link>
                                </SidebarMenuButton>
                            </li>
                        </ul>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </CollapsibleRoot>
        </SidebarMenu>
        <SidebarMenu v-else>
            <SidebarMenuItem v-for="item in group.items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
