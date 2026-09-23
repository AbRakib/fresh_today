<script setup lang="ts">
import {
    Notification,
    NotificationProgress,
    Notivue,
    NotivueSwipe,
} from 'notivue';
</script>

<template>
    <Notivue v-slot="item" position="bottom-center">
        <NotivueSwipe :item="item">
            <Notification :item="item">
                <NotificationProgress :item="item" />
            </Notification>
        </NotivueSwipe>
    </Notivue>
</template>
