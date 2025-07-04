<template>
  <!-- <div class="flex items-center justify-center h-[90vh]">
        <button @click="showMode = true"
            class="block text-red-500 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
            type="button">
            Toggle modal
        </button>
    </div> -->

  <div v-if="showMode" tabindex="-1">
    <div class="fixed inset-0 z-40 bg-black bg-opacity-50"></div>

    <div class="fixed inset-0 z-50 flex items-center justify-center">
      <div
        class="relative w-full max-w-[40vw] max-h-[80vh] overflow-y-auto p-6 rounded-lg shadow-xl bg-gray-800"
      >
        <button
          @click="$emit('close')"
          type="button"
          class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
          data-modal-hide="popup-modal"
        >
          <svg
            class="w-3 h-3"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 14 14"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
            />
          </svg>
          <span class="sr-only">Close</span>
        </button>

        <div class="p-4 text-center md:p-5">
          <svg
            class="w-12 h-12 mx-auto mb-6"
            :class="className"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 20 20"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>

          <h3
            class="mb-5 text-xl font-normal"
            :class="className"
            v-for="notice in notices"
            :key="notice"
          >
            {{ notice }}
          </h3>

          <input
            v-if="inputShow"
            type="text"
            :value="folderName"
            @input="$emit('update:folderName', $event.target.value)"
            placeholder="請輸入資料夾名稱"
            class="w-full px-3 py-2 mt-6 mb-6 border rounded"
          />

          <p v-if="!valFolderName && folderName !== ''" class="text-red-500 text-[1.5rem]">
            *僅接受中、英、數，不接受任何符號，且以 30 字元為限
          </p>

          <button
            @click="BT()"
            type="button"
            class="text-white bg-blue-600 hover:bg-blue-500 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-lg inline-flex items-center px-5 py-2.5 mt-6 text-center"
            :disabled="inputShow && (!valFolderName || folderName === '')"
          >
            OK!
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps([
  "inputShow",
  "folderName",
  "update:folderName",
  "notices",
  "showMode",
  "className",
]);
const emit = defineEmits(["close", "update:folderName", "emitFolderName"]);

const valFolderName = computed(() => /^[A-Za-z0-9\p{Script=Han}]{1,30}$/u.test(props.folderName));

function BT() {
  if (props.inputShow) {
    emit("emitFolderName");
  } else {
    emit("close");
  }
}
</script>
