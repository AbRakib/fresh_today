<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import {
    CollapsibleContent,
    CollapsibleRoot,
    CollapsibleTrigger,
} from 'reka-ui';
import { ref, watch } from 'vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavGroup } from '@/types';

const props = defineProps<{
    groups: NavGroup[];
}>();

const { currentUrl, isCurrentUrl } = useCurrentUrl();

const activeGroupTitle = () =>
    props.groups.find(
        (group) =>
            group.collapsible &&
            group.items.some((item) => isCurrentUrl(item.href)),
    )?.title ?? null;

const openGroupTitle = ref<string | null>(activeGroupTitle());

watch(currentUrl, () => {
    openGroupTitle.value = activeGroupTitle();
});
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
                :open="openGroupTitle === group.title"
                @update:open="
                    (isOpen) => {
                        openGroupTitle = isOpen ? group.title : null;
                    }
                "
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
                                class="ml-auto transition-transform duration-200 ease-out group-data-[state=open]/collapsible:rotate-90"
                            />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent
                        class="submenu-content overflow-hidden group-data-[collapsible=icon]:hidden"
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

<style scoped>
@keyframes submenu-slide-down {
    from {
        height: 0;
        opacity: 0;
    }
    to {
        height: var(--reka-collapsible-content-height);
        opacity: 1;
    }
}

@keyframes submenu-slide-up {
    from {
        height: var(--reka-collapsible-content-height);
        opacity: 1;
    }
    to {
        height: 0;
        opacity: 0;
    }
}

.submenu-content[data-state='open'] {
    animation: submenu-slide-down 200ms ease-out;
}

.submenu-content[data-state='closed'] {
    animation: submenu-slide-up 200ms ease-in;
}

@media (prefers-reduced-motion: reduce) {
    .submenu-content,
    .submenu-content[data-state='open'],
    .submenu-content[data-state='closed'] {
        animation: none;
    }
}
</style>
