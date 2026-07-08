<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <img
            v-if="$page.props.branding.logo_url"
            :src="$page.props.branding.logo_url"
            :alt="$page.props.branding.app_name"
            class="size-full object-contain"
        />
        <AppLogoIcon v-else class="size-5 fill-current text-white" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">
            {{ $page.props.branding.app_name }}
        </span>
    </div>
</template>
