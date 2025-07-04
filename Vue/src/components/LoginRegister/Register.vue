<template>
  <div
    class="absolute w-full h-full bg-white rounded-xl shadow-md flex flex-col items-center justify-center gap-4 p-6 [backface-visibility:hidden] [transform:rotateY(180deg)_translateZ(250px)]"
  >
    <h2 class="text-xl font-bold">註冊</h2>
    <input
      type="text"
      placeholder="使用者名稱"
      v-model="userName"
      class="w-full px-3 py-2 border rounded"
      :class="valUserName || !userName ? '' : 'border-red-500'"
    />
    <p v-if="!valUserName && userName != ''" class="text-red-500 text-[12px]">
      *僅接受英數<br />*長度為 5 到 100 字元之間
    </p>

    <input
      type="email"
      placeholder="電子信箱"
      v-model="email"
      class="w-full px-3 py-2 border rounded"
      :class="valEmail || !email ? '' : 'border-red-500'"
    />
    <p v-if="!valEmail && email != ''" class="text-red-500 text-[12px]">
      *請輸入正確的 Email 格式
    </p>

    <input
      type="password"
      placeholder="密碼"
      v-model="PW"
      class="w-full px-3 py-2 border rounded"
      :class="valPW || !PW ? '' : 'border-red-500'"
    />
    <p v-if="!valPW && PW != ''" class="text-red-500 text-[12px]">
      *密碼須包含英文大小寫、數字以及至少 3 種不同符號<br />*長度為 12 到 100
      字元之間
    </p>

    <input
      type="password"
      placeholder="確認密碼"
      v-model="CPW"
      class="w-full px-3 py-2 border rounded"
      :class="valCPW ? '' : 'border-red-500'"
    />
    <p v-if="!valCPW != ''" class="text-red-500 text-[12px]">與密碼不相符</p>

    <button
      @click="register()"
      class="w-full px-4 py-2 text-white bg-green-500 rounded"
      :disabled="!(valUserName && valEmail && valPW && valCPW && lock)"
    >
      註冊
    </button>
    <button @click="$emit('flip')" class="mt-2 text-blue-500" :disabled="!lock">
      返回登入
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import axios from "axios";

const emit = defineEmits(["flip", "notice"]);

const valUserName = computed(() => /^[A-Za-z0-9]{5,100}$/.test(userName.value));

const valEmail = computed(() =>
  /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email.value)
);

const valPW = computed(() => {
  const value = PW.value;
  const upper = /[A-Z]/.test(value);
  const lower = /[a-z]/.test(value);
  const digit = /\d/.test(value);
  const symbol = value.match(/[^A-Za-z0-9]/g) || [];
  const symbols = new Set(symbol).size >= 3;
  const long = value.length >= 12 && value.length <= 100;

  return upper && lower && digit && symbols && long;
});

const valCPW = computed(() => PW.value === CPW.value);

const clearInput = () => {
  email.value = "";
  userName.value = "";
  PW.value = "";
  CPW.value = "";
};

const userName = ref("");
const email = ref("");
const PW = ref("");
const CPW = ref("");
const lock = ref(true);
const className = ref("");
const response = ref("");

async function register() {
  lock.value = false;
  try {
    const data = {
      username: userName.value,
      email: email.value,
      password: PW.value,
    };

    const r = await axios.post(
      `http://localhost:8000/api/accounts/register`,
      data,
      { withCredentials: true }
    );
    if (r.data["message"] === "success") {
      className.value = "text-white";
      response.value = ["請驗證電子信箱地址以完成註冊!"];
      clearInput();
      emit('flip');
    } else {
      //   console.log(r.data);
      className.value = "text-red-500";
      response.value = ["註冊失敗，請稍後再試。"];
    }
  } catch (e) {
    if (e.response && e.response.status === 422 && e.response.data.errors) {
      if (e.response.status === 429) {
        response.value = ["請求過多，請稍後再試!"];
      } else {
        response.value = Object.values(e.response.data).flat();
      }
    } else {
      //   response.value = Object.values(e.response.data.errors).flat();
      console.log(e);
      response.value = ["註冊失敗，請稍後再試。"];
    }
    className.value = "text-red-500";
  }

  lock.value = true;
  emit("notice", response.value, className);
}
</script>
