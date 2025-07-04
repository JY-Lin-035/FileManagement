<template>
  <div class="h-screen w-screen flex justify-center items-center">
    <div class="min-h-[70vh] w-[70vw] flex justify-center items-center">
      <div class="flex flex-col mr-6">
        <button
          type="button"
          @click="mode = true"
          class="text-white bg-gradient-to-r from-cyan-500 to-blue-500 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"
        >
          信箱地址
        </button>
        <button
          type="button"
          @click="mode = false"
          class="text-white bg-gradient-to-r from-cyan-500 to-blue-500 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"
        >
          密碼
        </button>
      </div>

      <div class="w-[50%]">
        <ol class="relative border-s border-gray-700">
          
          <Mail  v-if="mode"/>
          <Password  v-if="!mode"/>
          
        </ol>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Mail from './ModifyOptions/Mail.vue'
import Password from './ModifyOptions/Password.vue'

const mode = ref(true);
</script>