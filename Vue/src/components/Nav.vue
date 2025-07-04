<template>
    <nav class="border-gray-200 bg-gray-800">
        <div class="flex flex-wrap items-center justify-between w-full mx-auto py-[1rem] px-[2rem]">
            <a href="#" @click.prevent="icoLink()" class="flex items-center space-x-3 rtl:space-x-reverse">
                <img src="../assets/J.png" class="w-auto h-16" alt="Logo" />
                <span class="self-center text-[2rem] font-semibold whitespace-nowrap text-white">Meow</span>
            </a>
            
            <Drawer v-if="route.path != '/' && route.path != '/forgetPassword' && !route.path.startsWith('/share/')"/>
        </div>
    </nav>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import { Base64 } from "js-base64";

import Drawer from './Drawer.vue';



const route = useRoute();
const router = useRouter()
function icoLink() {
    if (route.path === '/' || route.path === '/forgetPassword') {
        router.push('/');
    } else {
        router.push(`/fileList/${Base64.encodeURI('Home')}`);
    }
}
</script>