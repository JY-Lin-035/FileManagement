<template>
  <div
    class="absolute w-full h-full bg-white rounded-xl shadow-md flex flex-col justify-center items-center gap-4 p-6 [backface-visibility:hidden] [transform:translateZ(250px)]"
  >
    <div class="flex-1"></div>
    <h2 class="text-xl font-bold">登入</h2>
    <input
      type="text"
      placeholder="使用者名稱"
      v-model="userName"
      class="border w-full px-3 py-2 rounded mt-[30px]"
    />
    <input
      type="password"
      placeholder="密碼"
      v-model="PW"
      class="w-full px-3 py-2 border rounded"
    />
    <button
      @click="login()"
      class="bg-blue-500 text-white px-4 py-2 rounded w-full mt-[30px]"
      :disabled="!(userName && PW && lock)"
    >
      登入
    </button>
    <span class="flex justify-between w-full mt-[30px]">
      <button @click="goForgetPW()" class="text-blue-500" :disabled="!lock">
        忘記密碼?
      </button>
      <button @click="$emit('flip')" class="text-blue-500" :disabled="!lock">
        註冊
      </button>
    </span>
    <div class="flex-1"></div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { Base64 } from "js-base64";

import axios from "axios";

const emit = defineEmits(["flip", "notice"]);
const router = useRouter();

const userName = ref("");
const PW = ref("");
const className = ref("");
const response = ref("");
const lock = ref(true);

// TODO: Login
async function login() {
  lock.value = false;

  if (userName.value === "" || PW.value === "") {
    className.value = "text-red-500";
    response.value = ["使用者名稱和密碼不能為空!"];
    showMode.value = true;
    lock.value = true;
    return;
  }
  try {
    const data = {
      username: userName.value,
      password: PW.value,
    };

    const r = await axios.post(
      `http://localhost:8000/api/accounts/login`,
      data,
      { withCredentials: true }
    );
    // console.log(r.data.status);
    if (r.status === 200) {
      localStorage.setItem("email", r.data["email"]);
      userName.value = "";
      PW.value = "";
      router.push(`/fileList/${Base64.encodeURI("Home")}`);
    } else {
      // response.value = [r.response.status];
      response.value = ["登入失敗，請檢查帳號和密碼。"];
      className.value = "text-white";
      emit("notice", response.value, className);
    }
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
      response.value = ["登入失敗，請稍後再試。"];
      //   response.value = [e];
    }

    className.value = "text-red-500";
    emit("notice", response.value, className);
  }
  lock.value = true;
}

// TODO: forgetPW
const goForgetPW = () => router.push("/forgetPassword");
</script>
