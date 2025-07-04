<template>
  <div
    @click="copyShow = false"
    class="flex w-full h-full flex-col justify-center items-center"
  >
    <Notice
      :notices="response"
      :showMode="showMode"
      :className="className"
      @close="showMode = false"
      @emitFolderName="showMode = false"
    />

    <div
      class="w-[80vw] mx-auto mt-[5%] flex-1 transition-all duration-[900ms] ease-in-out"
      :class="IN ? 'opacity-100' : 'opacity-0'"
    >
      <div class="mb-4 flex justify-between items-center">
        <span>
          <input
            v-model="search"
            type="text"
            placeholder="搜尋"
            class="border rounded px-3 py-1 w-64"
          />
        </span>

        <span class="text-sm text-gray-500"
          >共 {{ shareList.length }} 筆, 每頁 &nbsp;
          <select v-model="perPage" class="border rounded px-1 py-1">
            <option v-for="n in [5, 10, 15, 20, 50]" :key="n" :value="n">
              {{ n }}
            </option>
          </select>
          &nbsp; 筆
        </span>
      </div>

      <div
        class="max-h-[60vh] hide-scrollbar overflow-x-auto overflow-y-auto shadow-[0.8rem_0.8rem_2.5rem_white] rounded-[2rem] border-2 border-white"
      >
        <table class="w-full table-fixed text-left border border-collapse rounded-[2rem]">
          <thead>
            <tr class="bg-blue-200 text-[1.5rem] text-center">
              <th
                @click.stop="changeSortType('date')"
                class="cursor-pointer p-2 w-[10%] border"
              >
                <span class="inline-flex items-center gap-1">
                  時間
                  <svg
                    class="w-6 h-6 text-gray-800"
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
                      d="m8 10 4-6 4 6H8Zm8 4-4 6-4-6h8Z"
                    />
                  </svg>
                </span>
              </th>
              <th
                @click.stop="changeSortType('name')"
                class="cursor-pointer p-2 w-[35%] border"
              >
                <span class="inline-flex items-center gap-1">
                  名稱
                  <svg
                    class="w-6 h-6 text-gray-800 inline-block"
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
                      d="m8 10 4-6 4 6H8Zm8 4-4 6-4-6h8Z"
                    />
                  </svg>
                </span>
              </th>
              <th
                @click.stop="changeSortType('path')"
                class="cursor-pointer p-2 w-[35%] border"
              >
                <span class="inline-flex items-center gap-1">
                  路徑
                  <svg
                    class="w-6 h-6 text-gray-800 inline-block"
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
                      d="m8 10 4-6 4 6H8Zm8 4-4 6-4-6h8Z"
                    />
                  </svg>
                </span>
              </th>
              <th class="p-2 w-[20%] border">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in pageList"
              :key="item.link"
              class="bg-gray-300 hover:bg-blue-200"
            >
              <td class="p-2 text-[1.2rem] text-center border break-words whitespace-normal">
                {{ item.date }}
              </td>
              <td class="p-2 text-[1.2rem] border break-words whitespace-normal">
                <svg
                  class="inline w-6 h-6 ml-5 text-white"
                  aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke="currentColor"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10 3v4a1 1 0 0 1-1 1H5m14-4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z"
                  />
                </svg>
                {{ item.name }}
              </td>
              <td class="p-2 text-[1.2rem] border break-words whitespace-normal">
                {{ item.path }}
              </td>
              <td class="p-2 text-[1.2rem] text-center border break-words whitespace-normal">
                <span class="flex justify-center sm:gap-2 md:gap-6 lg:gap-10">
                  <div
                    class="relate w-max text-sm text-white rounded"
                  >
                    <span>
                      <button
                        class="mr-8 p-2 rounded-[0.5rem] bg-blue-400"
                        @click.stop="copyFunc(item.link)"
                      >
                        複製連結
                      </button>
                      <button
                        class="p-2 rounded-[0.5rem] bg-red-400"
                        @click.stop="deleteLink(item.link)"
                      >
                        移除
                      </button>
                    </span>
                  </div>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-auto mb-4 flex justify-center items-center gap-2">
      <button
        :disabled="page === 1"
        @click="page--"
        class="px-3 py-1 border rounded disabled:opacity-50"
      >
        <svg
          class="w-5 h-5 rtl:rotate-180"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 6 10"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M5 1 1 5l4 4"
          />
        </svg>
      </button>
      <span>第 {{ page }} / {{ totalPages }} 頁</span>
      <button
        :disabled="page === totalPages || totalPages === 0"
        @click="page++"
        class="px-3 py-1 border rounded disabled:opacity-50"
      >
        <svg
          class="w-5 h-5 rtl:rotate-180"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 6 10"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="m1 9 4-4-4-4"
          />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onBeforeMount, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";

import axios from "axios";
import Notice from "../Notices.vue";

const router = useRouter();
const route = useRoute();

const shareList = ref([]);

async function getShareList() {
  try {
    const r = await axios.get(`http://localhost:8000/api/share/getList`, {
      withCredentials: true,
    });

    if (r.status === 200) {
      shareList.value = r.data["share"];
      localStorage.setItem("previousPath", route.path);
    } else {
      localStorage.clear();
      router.push("/");
    }
  } catch (e) {
    console.log(e);
    localStorage.clear();
    router.push("/");
  }
}

onBeforeMount(() => {
  getShareList();
});

const IN = ref(false);
onMounted(() => {
  setTimeout(() => {
    IN.value = true;
  }, 200);
});

// search and sort
const search = ref("");
const sortType = ref("name");
const sortUpDown = ref(true);

const filterShareList = computed(() => {
  page.value = 1;
  let fList = shareList.value.filter((f) =>
    f.name.toLowerCase().includes(search.value.toLowerCase())
  );

  fList.sort((a, b) => {
    return sortUpDown.value
      ? a[sortType.value].localeCompare(b[sortType.value])
      : b[sortType.value].localeCompare(a[sortType.value]);
  });

  return fList;
});

function changeSortType(type) {
  if (sortType.value === type) {
    sortUpDown.value = !sortUpDown.value;
  } else {
    sortType.value = type;
    sortUpDown.value = true;
  }
}

// 分頁
const page = ref(1);
const perPage = ref(10);
const previousPerPage = ref(10);

const totalPages = computed(() =>
  Math.ceil(shareList.value.length / perPage.value)
);
const pageList = computed(() => {
  if (perPage.value > previousPerPage.value) {
    page.value = 1;
    previousPerPage.value = perPage.value;
  }
  else {
    previousPerPage.value = perPage.value;    
  }
  const start = (page.value - 1) * perPage.value;
  return filterShareList.value.slice(start, start + perPage.value);
});

const showMode = ref(false);
const className = ref("");
const response = ref("");

// 移除檔案分享
async function deleteLink(link) {
  try {
    const r = await axios.delete(
      `http://localhost:8000/api/share/deleteLink?link=${link}`,
      {
        withCredentials: true,
      }
    );

    const share = shareList.value.findIndex(
      (item) => item.link === link
    );

    if (share !== -1) {
      shareList.value.splice(share, 1);
    }

    response.value = ["移除成功!"];
  } catch (e) {
    // console.error(e);
    response.value = ["連結移除失敗, 請稍後再試!"];
  }

  className.value = "text-red-500";
  showMode.value = true;
}

// 複製
const copyFunc = (m) => {
  navigator.clipboard.writeText("http://localhost:5173/share/" + m);
  copyShow.value = false;
};
</script>

<style>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
