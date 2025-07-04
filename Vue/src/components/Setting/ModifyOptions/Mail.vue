<template>
  <Notice
    :notices="response"
    :showMode="showMode"
    :className="className"
    @close="(showMode = false), (lock = true)"
  />
  <li class="mb-10 ms-4">
    <div
      class="absolute w-3 h-3 bg-gray-700 rounded-full mt-1.5 -start-1.5 border border-white"
    ></div>
    <time class="mb-1 text-[3rem] font-normal leading-none text-[#1d92ff]"
      >修改信箱地址
    </time>

    <h3 class="text-sm mt-6 tracking-widest font-semibold text-[red]">
      目前電子信箱地址: {{ nowEmail }}
    </h3>

    <input
      type="email"
      placeholder="驗證目前電子信箱"
      v-model="checkNowEmail"
      class="w-[70%] px-3 py-2 border rounded mt-[10px] mr-3"
      :class="valEmail1 || !checkNowEmail ? '' : 'border-red-500'"
    />

    <p
      v-if="!valEmail1 && checkNowEmail != ''"
      class="text-red-500 text-[12px]"
    >
      *請輸入正確的 Email 格式
    </p>

    <div>
      <input
        type="email"
        placeholder="電子信箱"
        v-model="newEmail"
        class="w-[70%] px-3 py-2 border rounded mt-[10px] mr-3"
        :class="valEmail2 || !newEmail ? '' : 'border-red-500'"
      />

      <button
        type="button"
        @click="getCode()"
        class="text-white bg-gradient-to-br from-green-400 to-blue-600 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"
        :disabled="!valEmail2 || waitCode"
      >
        {{ waitCode ? "傳送驗證碼(" + waitTime + ")" : "傳送驗證碼" }}
      </button>
    </div>
    <p v-if="!valEmail2 && newEmail != ''" class="text-red-500 text-[12px]">
      *請輸入正確的 Email 格式
    </p>

    <span>
      <input
        type="text"
        placeholder="輸入驗證碼"
        v-model="code"
        class="px-3 py-2 mt-4 mr-3 border rounded"
        :class="valCode ? '' : 'border-red-500'"
      />

      <button
        type="button"
        @click="modifyMail()"
        class="text-white bg-gradient-to-br from-green-400 to-blue-600 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"
        :disabled="!code || !newEmail"
      >
        確認修改
      </button>
    </span>

    <p v-if="!valCode" class="text-red-500 text-[12px]">驗證碼錯誤</p>
  </li>
</template>

<script setup>
import { ref, computed } from "vue";
import { onMounted } from "vue";
import axios from "axios";
import Notice from "../../Notices.vue";

onMounted(() => {
  nowEmail.value = localStorage.getItem("email");
});

const lock = ref(true);
const showMode = ref(false);
const className = ref("");
const response = ref("");

const nowEmail = ref("");
const checkNowEmail = ref("");
const newEmail = ref("");

const valEmail1 = computed(() =>
  /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(checkNowEmail.value)
);

const valEmail2 = computed(() =>
  /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(newEmail.value)
);

const code = ref("");
const valCode = ref(true);
const waitCode = ref(false);
let waitTime = ref(30);

async function getCode() {
  lock.value = false;

  if (newEmail.value === "") {
    className.value = "text-red-500";
    response.value = ["電子信箱不能為空!"];
    showMode.value = true;
    lock.value = true;
    return;
  }

  try {
    const data = {
      mode: "mail",
      email: newEmail.value,
    };

    waitCode.value = true;
    const timer = setInterval(() => {
      if (waitTime.value > 0) {
        waitTime.value--;
      } else {
        clearInterval(timer);
        waitTime.value = 30;
        waitCode.value = false;
      }
    }, 1000);

    const r = await axios.post(
      `http://localhost:8000/api/accounts/getCode`,
      data,
      { withCredentials: true }
    );
    if (r.status === 200) {
      className.value = "text-white";
      response.value = [r.data.message];
    } else {
      response.value = ["傳送失敗，請檢查信箱地址。"];
      className.value = "text-white";
    }
    showMode.value = true;
  } catch (e) {
    if (
      e.response &&
      e.response.status >= 400 &&
      e.response.status < 500 &&
      e.response.data
    ) {
      if (e.response.status === 429) {
        response.value = ["請求過多，請稍後再試!"];
      } else {
        response.value = Object.values(e.response.data).flat();
      }
    } else {
      // response.value = Object.values(e.response.data.errors).flat();
      console.log(e);
    }

    className.value = "text-red-500";
    showMode.value = true;
  }
  lock.value = true;
}

async function modifyMail() {
  lock.value = false;

  if (
    checkNowEmail.value === "" ||
    newEmail.value === "" ||
    code.value === ""
  ) {
    className.value = "text-red-500";
    response.value = ["電子信箱或驗證碼不能為空!"];
    showMode.value = true;
    lock.value = true;
    return;
  }

  try {
    const data = {
      checkEmail: checkNowEmail.value,
      email: newEmail.value,
      code: code.value,
    };

    const r = await axios.put(
      `http://localhost:8000/api/accounts/modifyMail`,
      data,
      { withCredentials: true }
    );
    if (r.status === 200) {
      nowEmail.value = r.data["email"];
      localStorage.setItem("email", nowEmail.value);
      className.value = "text-white";
      response.value = ["修改成功!"];
      nowEmail = "";
      checkNowEmail = "";
      newEmail = "";
      code = "";
    } else {
      className.value = "text-white";
      response.value = ["修改失敗，請稍後再試!"];
    }
    showMode.value = true;
  } catch (e) {
    if (
      e.response &&
      e.response.status >= 400 &&
      e.response.status < 500 &&
      e.response.data
    ) {
      response.value = Object.values(e.response.data).flat();
    } else {
      // response.value = Object.values(e.response.data.errors).flat();
      console.log(e);
    }

    className.value = "text-red-500";
    showMode.value = true;
  }
  lock.value = true;
}
</script>
