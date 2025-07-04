<template>
  <div
    @click="copyShow = false"
    class="flex w-full h-full flex-col justify-center items-center"
  >
    <Notice
      :inputShow="inputShow"
      :folderName="folderName"
      :notices="response"
      :showMode="showMode"
      :className="className"
      @update:folderName="folderName = $event"
      @close="(showMode = false), (folderName = ''), (waitFolderName = [])"
      @emitFolderName="
        (showMode = false), (inputShow = false), emitFolderName()
      "
    />

    <UpLoad
      v-show="showUpLoad"
      :PATH="PATH"
      @hidden="showUpLoad = false"
      @refresh="getFileList(route.params.folderName)"
      @complete="
        (res, cn) => {
          response = res;
          className = cn;
          showMode = true;
        }
      "
    />

    <Breadcrumb :PATH="PATH" />

    <div
      class="w-[80vw] mx-auto mt-[1%] flex-1 transition-all duration-[900ms] ease-in-out"
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

          <svg
            name="create"
            class="w-9 h-9 ml-6 mr-4 text-yellow-300 inline cursor-pointer"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
            @click.stop="
              (response = ''),
                (className = 'text-white'),
                (inputShow = true),
                (showMode = true),
                (waitFolderName = [`${Base64.encodeURI(PATH)}`, null, 'create'])
            "
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M14 8H4m8 3.5v5M9.5 14h5M4 6v13a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1h-5.032a1 1 0 0 1-.768-.36l-1.9-2.28a1 1 0 0 0-.768-.36H5a1 1 0 0 0-1 1Z"
            />
          </svg>

          <svg
            name="upload"
            class="w-9 h-9 text-green-600 inline cursor-pointer"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
            @click.stop="showUpLoad = true"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 15v2a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-2M12 4v12m0-12 4 4m-4-4L8 8"
            />
          </svg>
        </span>

        <span class="text-sm text-gray-500"
          >共 {{ filterList.length }} 筆, 每頁 &nbsp;
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
        <table
          class="w-full table-fixed text-left border border-collapse rounded-[2rem]"
        >
          <thead>
            <tr class="bg-blue-200 text-[1.5rem] text-center">
              <th
                @click="changeSortType('date')"
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
                @click="changeSortType('name')"
                class="cursor-pointer p-2 w-[45%] border"
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
                @click="changeSortType('size')"
                class="cursor-pointer p-2 w-[25%] border"
              >
                <span class="inline-flex items-center gap-1">
                  大小
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
              :key="PATH + item.name + item.date"
              class="bg-gray-300 hover:bg-blue-200"
              :class="item.type === 'folder' ? 'cursor-pointer' : ''"
              @click="
                item.type === 'folder' &&
                  router.push(
                    `/fileList/${Base64.encodeURI(PATH + '-' + item.name)}`
                  )
              "
            >
              <td
                class="p-2 text-[1.2rem] text-center border break-words whitespace-normal"
              >
                {{ item.date }}
              </td>
              <td
                class="p-2 text-[1.2rem] border break-words whitespace-normal"
              >
                <span v-if="item.type === 'folder'">
                  <svg
                    class="inline w-6 h-6 ml-5 text-yellow-200"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M4 4a2 2 0 0 0-2 2v12a2 2 0 0 0 .087.586l2.977-7.937A1 1 0 0 1 6 10h12V9a2 2 0 0 0-2-2h-4.532l-1.9-2.28A2 2 0 0 0 8.032 4H4Zm2.693 8H6.5l-3 8H18l3-8H6.693Z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </span>
                <span v-else>
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
                </span>
                {{ item.name }}
              </td>
              <td
                class="p-2 text-[1.2rem] border break-words whitespace-normal"
                :class="item.type === 'folder' ? 'text-center' : ' text-right'"
              >
                {{ item.size || "-" }}
              </td>
              <td
                class="p-2 text-[1.2rem] text-center border break-words whitespace-normal"
              >
                <span class="flex justify-center sm:gap-2 md:gap-6 lg:gap-10">
                  <svg
                    v-if="item.type === 'file'"
                    name="download"
                    class="w-6 h-6 text-green-600 cursor-pointer"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    fill="none"
                    viewBox="0 0 24 24"
                    @click.stop="
                      callDownloadFile(
                        `${Base64.encodeURI(PATH)}`,
                        item.name,
                        item.type
                      )
                    "
                  >
                    <path
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 15v2a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-2m-8 1V4m0 12-4-4m4 4 4-4"
                    />
                  </svg>

                  <svg
                    v-else
                    name="rename"
                    class="w-6 h-6 text-green-600 cursor-pointer"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    fill="none"
                    viewBox="0 0 24 24"
                    @click.stop="
                      (response = ''),
                        (className = 'text-white'),
                        (inputShow = true),
                        (showMode = true),
                        (waitFolderName = [
                          `${Base64.encodeURI(PATH)}`,
                          item.name,
                          'rename',
                        ])
                    "
                  >
                    <path
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"
                    />
                  </svg>

                  <div class="relative group">
                    <svg
                      v-if="item.type === 'file'"
                      name="share"
                      class="w-6 h-6 text-blue-500 cursor-pointer"
                      aria-hidden="true"
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      fill="currentColor"
                      viewBox="0 0 24 24"
                      @click.stop="
                        callShareFileLink(
                          `${Base64.encodeURI(PATH)}`,
                          item.name
                        )
                      "
                    >
                      <path
                        d="M5.027 10.9a8.729 8.729 0 0 1 6.422-3.62v-1.2A2.061 2.061 0 0 1 12.61 4.2a1.986 1.986 0 0 1 2.104.23l5.491 4.308a2.11 2.11 0 0 1 .588 2.566 2.109 2.109 0 0 1-.588.734l-5.489 4.308a1.983 1.983 0 0 1-2.104.228 2.065 2.065 0 0 1-1.16-1.876v-.942c-5.33 1.284-6.212 5.251-6.25 5.441a1 1 0 0 1-.923.806h-.06a1.003 1.003 0 0 1-.955-.7A10.221 10.221 0 0 1 5.027 10.9Z"
                      />
                    </svg>

                    <div
                      v-show="response === item.name && copyShow"
                      class="absolute bottom-full left-1/2 transform -translate-x-1/2 mt-2 w-max px-2 py-1 text-sm text-white rounded opacity-100 transition-opacity duration-300"
                    >
                      <span>
                        <button
                          class="mr-2 p-2 rounded-[0.5rem] bg-blue-400"
                          @click.stop="copyFunc(shareFileLink)"
                        >
                          複製
                        </button>
                        <button
                          class="p-2 rounded-[0.5rem] bg-red-400"
                          @click.stop="
                            callDeleteShareFileLink(
                              `${Base64.encodeURI(PATH)}`,
                              item.name
                            ),
                              (copyShow = false)
                          "
                        >
                          移除
                        </button>
                      </span>
                    </div>
                  </div>

                  <svg
                    name="delete"
                    class="w-6 h-6 text-red-500 cursor-pointer"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    @click.stop="
                      item.type === 'file'
                        ? callDeleteFile(
                            `${Base64.encodeURI(PATH)}`,
                            item.name,
                            item
                          )
                        : callDeleteFolder(
                            `${Base64.encodeURI(PATH)}`,
                            item.name
                          )
                    "
                  >
                    <path
                      fill-rule="evenodd"
                      d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z"
                      clip-rule="evenodd"
                    />
                  </svg>
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
import { ref, computed, onBeforeMount, onMounted, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import { Base64 } from "js-base64";
import Breadcrumb from "./Breadcrumb.vue";
import {
  downloadFile,
  deleteFile,
  getShareFileLink,
  deleteShareFileLink,
} from "../../helpers/fileOperations";
import {
  createFolder,
  renameFolder,
  deleteFolder,
} from "../../helpers/folderOperations";

import axios from "axios";
import Notice from "../Notices.vue";
import UpLoad from "./UpLoad.vue";

const router = useRouter();
const route = useRoute();

const showUpLoad = ref(false);

const PATH = ref("");
const fileList = ref([]);

// getFileList
async function getFileList(base) {
  try {
    const r = await axios.get(
      `http://localhost:8000/api/files/getFileList?path=${route.params.folderName}`,
      { withCredentials: true }
    );
    if (r.status === 200) {
      fileList.value = r.data["file"];
      PATH.value = Base64.decode(base);
    } else {
      localStorage.clear();
      router.push("/");
    }
  } catch (e) {
    // console.log(e);
    localStorage.clear();
    router.push("/");
  }
}

onBeforeMount(() => {
  getFileList(route.params.folderName);
});

watch(
  () => route.params.folderName,
  (base) => {
    getFileList(base);
  }
);

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

const filterList = computed(() => {
  page.value = 1;
  let fList = fileList.value.filter((f) =>
    f.name.toLowerCase().includes(search.value.toLowerCase())
  );

  fList.sort((a, b) => {
    if (a.type === "folder" && b.type !== "folder") return -1;
    if (a.type !== "folder" && b.type === "folder") return 1;

    if (sortType.value === "size" && a.type === "file" && b.type === "file") {
      const aSplit = a["size"].split(" ");
      const bSplit = b["size"].split(" ");

      if (aSplit[1].localeCompare(bSplit[1]) === 0) {
        return sortUpDown.value
          ? Number(aSplit[0]) - Number(bSplit[0])
          : Number(bSplit[0]) - Number(aSplit[0]);
      } else {
        const units = { B: 0, KB: 1, MB: 2, GB: 3, TB: 4 };
        const A = units[aSplit[1]];
        const B = units[bSplit[1]];

        return sortUpDown.value ? A - B : B - A;
      }
    }

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
  Math.ceil(filterList.value.length / perPage.value)
);
const pageList = computed(() => {
  if (perPage.value > previousPerPage.value) {
    page.value = 1;
    previousPerPage.value = perPage.value;
  } else {
    previousPerPage.value = perPage.value;
  }
  const start = (page.value - 1) * perPage.value;
  return filterList.value.slice(start, start + perPage.value);
});

const showMode = ref(false);
const className = ref("");
const response = ref("");

// 下載
function callDownloadFile(dir, fileName) {
  [response.value, className.value, showMode.value] = downloadFile(
    dir,
    fileName
  );
}

// 刪除檔案
async function callDeleteFile(dir, fileName, item) {
  [response.value, className.value, showMode.value, fileList.value] =
    await deleteFile(dir, fileName, item, fileList.value);
}

// 檔案分享
const shareFileLink = ref("");
const copyShow = ref(false);
async function callShareFileLink(dir, fileName) {
  [
    response.value,
    className.value,
    showMode.value,
    shareFileLink.value,
    copyShow.value,
  ] = await getShareFileLink(dir, fileName);
}

// 移除檔案分享
async function callDeleteShareFileLink(dir, fileName) {
  [response.value, className.value, showMode.value] = await deleteShareFileLink(
    dir,
    fileName
  );
}

// 複製
const copyFunc = (m) => {
  navigator.clipboard.writeText("http://localhost:5173/share/" + m);
  copyShow.value = false;
};

// 新增、改名 資料夾
const folderName = ref("");
const inputShow = ref(false);
const waitFolderName = ref([]);

async function emitFolderName() {
  if (waitFolderName.value[2] === "rename") {
    [response.value, className.value, fileList.value] = await renameFolder(
      waitFolderName.value[0],
      waitFolderName.value[1],
      folderName.value,
      fileList.value
    );
  } else if (waitFolderName.value[2] === "create") {
    [response.value, className.value, fileList.value] = await createFolder(
      waitFolderName.value[0],
      folderName.value,
      fileList.value
    );
  }

  showMode.value = true;
}

// 刪除資料夾
async function callDeleteFolder(dir, itemName) {
  [response.value, className.value, showMode.value, fileList.value] =
    await deleteFolder(dir, itemName, fileList.value);
}
</script>

<style>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>