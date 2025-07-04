<template>
    <div class="fixed top-0 left-0 w-screen h-screen bg-gray-500 z-[-5] opacity-50"></div>
    <div class="flex flex-col h-screen">
        <router-view name="nav" class="flex-0" />
        <router-view name="main" class="flex-1 min-h-[700px]" />
        <router-view name="footer" class="flex-0" />
    </div>
</template>



<script setup>

import { RouterView } from 'vue-router';

</script>