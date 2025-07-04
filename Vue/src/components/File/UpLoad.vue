<template>
  <div class="fixed inset-0 z-20 flex items-center justify-center">
    <div
      class="absolute inset-0 bg-black bg-opacity-50"
      @click="$emit('hidden')"
    ></div>

    <div class="relative z-10">
      <div ref="uppyContainer"></div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from "vue";
import Uppy from "@uppy/core";
import Dashboard from "@uppy/dashboard";
import XHRUpload from "@uppy/xhr-upload";

import "@uppy/core/dist/style.css";
import "@uppy/dashboard/dist/style.css";

import { useStorage } from "../../stores/storage";

const props = defineProps(["PATH"]);
const emit = defineEmits(["hidden", "refresh", "complete"]);

const storage = useStorage();

const uppyContainer = ref(null);
let uppyInstance = null;

onMounted(() => {
  uppyInstance = new Uppy({
    restrictions: {
      maxFileSize: storage.signalStorage,
      allowedFileTypes: null,
      maxNumberOfFiles: 3,
    },
    autoProceed: false,
  });

  uppyInstance.use(Dashboard, {
    inline: true,
    target: uppyContainer.value,
    proudlyDisplayPoweredByUppy: false,
    locale: {
      strings: {
        dropPasteFiles: "請將檔案拖曳到這裡或 %{browseFiles}",
        browseFiles: "選擇檔案",
      },
    },
  });

  uppyInstance.use(XHRUpload, {
    endpoint: "http://localhost:8000/api/files/uploadFile",
    formData: true,
    fieldName: "file",
    bundle: false,
    withCredentials: true,
  });

  uppyInstance.on("file-added", (file) => {
    uppyInstance.setFileMeta(file.id, {
      dir: props.PATH,
    });
  });

  uppyInstance.on("upload-error", (file, error, response) => {
    let e = error.message;

    if (response) {
      if (response.body && response.body.message) {
        e = response.body.message;
      } else if (response.status) {
        e = `HTTP ${response.status}: ${response.statusText || e}`;
      }
    }

    alert(`檔案 ${file.name} 上傳失敗：${e}`);
  });

  //   uppyInstance.on("upload-success", (file, response) => {
  //   });

  uppyInstance.on("complete", (result) => {
    console.log(result.failed, result.successful);
    if (result.failed.length === 0) {
      emit("refresh");
      emit("complete", ["上傳完成!"], "text-green-500");
      storage.getFromAPI();
      uppyInstance.cancelAll();
    //   uppyInstance.destroy();
    }
  });
});

onBeforeUnmount(() => {
  if (uppyInstance) {
    try {
      uppyInstance.cancelAll();
      uppyInstance.destroy();
    } catch (e) {
      // console.log(e);
    }
    uppyInstance = null;
  }
});
</script>
