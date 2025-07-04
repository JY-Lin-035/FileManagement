<template>
  <div class="flex flex-col justify-center items-center bg-gray-300">
    <h1
      class="text-[5rem] text-blue-400 font-extrabold tracking-tight leading-none"
    >
      若未開始下載請點擊下方按鈕
    </h1>

    <div
      class="mt-[5vh] flex justify-center items-center bg-green-100 rounded-[6rem] p-6 cursor-pointer"
      @click="get()"
    >
      <svg
        name="download"
        class="w-[10vh] h-[10vh] mr-2 text-green-600 inline-block align-middle"
        aria-hidden="true"
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        fill="none"
        viewBox="0 0 24 24"
      >
        <path
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M4 15v2a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-2m-8 1V4m0 12-4-4m4 4 4-4"
        />
      </svg>
      <span class="text-[3.2rem] align-middle text-green-600">開始下載</span>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
const route = useRoute();

const showMode = ref(false);
const className = ref("");
const response = ref("");

const get = () => {
  try {
    const url = `http://localhost:8000/api/share/downloadFile?link=${
      route.params.link
    }&t=${Date.now()}`;

    const tempLink = document.createElement("a");
    tempLink.href = url;
    tempLink.download = "UnKnown";

    document.body.appendChild(tempLink);
    tempLink.click();
    document.body.removeChild(tempLink);

    return;
  } catch (e) {
    console.error(e);
    response.value = ["檔案不存在!"];
    className.value = "text-red-500";
    showMode.value = true;

    return;
  }
};

onMounted(() => {
  get();
});
</script>
