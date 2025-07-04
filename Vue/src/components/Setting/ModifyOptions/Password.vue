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
      >修改密碼
    </time>

    <input
      type="password"
      placeholder="目前密碼"
      v-model="nowPW"
      class="w-full px-3 py-2 mt-6 mb-3 border rounded"
    />

    <input
      type="password"
      placeholder="新密碼"
      v-model="newPW"
      class="w-full px-3 py-2 mb-3 border rounded"
      :class="valNewPW || !newPW ? '' : 'border-red-500'"
    />
    <p v-if="!valNewPW && newPW != ''" class="text-red-500 text-[12px] mb-4">
      *密碼須包含英文大小寫、數字以及至少 3 種不同符號<br />*長度為 12 到 100
      字元之間
    </p>

    <input
      type="password"
      placeholder="確認新密碼"
      v-model="cNewPW"
      class="w-full px-3 py-2 mb-2 border rounded"
      :class="valCNewPW ? '' : 'border-red-500'"
    />

    <div class="flex justify-between">
      <p v-if="valCNewPW"></p>
      <p v-if="!valCNewPW != ''" class="text-red-500 text-[12px]">
        與密碼不相符
      </p>

      <button
        type="button"
        @click="modifyPW()"
        class="text-white bg-gradient-to-br from-green-400 to-blue-600 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"
        :disabled="!nowPW || !newPW || !valCNewPW"
      >
        確認修改
      </button>
    </div>
  </li>
</template>

<script setup>
import { ref, computed } from "vue";
import axios from "axios";
import Notice from "../../Notices.vue";

const lock = ref(true);
const showMode = ref(false);
const className = ref("");
const response = ref("");

const nowPW = ref("");
const newPW = ref("");
const cNewPW = ref("");

const valNewPW = computed(() => {
  const value = newPW.value;
  const upper = /[A-Z]/.test(value);
  const lower = /[a-z]/.test(value);
  const digit = /\d/.test(value);
  const symbol = value.match(/[^A-Za-z0-9]/g) || [];
  const symbols = new Set(symbol).size >= 3;
  const long = value.length >= 12 && value.length <= 100;

  return upper && lower && digit && symbols && long;
});

const valCNewPW = computed(() => newPW.value === cNewPW.value);

async function modifyPW() {
  lock.value = false;

  if (nowPW.value === "" || newPW.value === "" || cNewPW.value === "") {
    className.value = "text-red-500";
    response.value = ["密碼不能為空!"];
    showMode.value = true;
    lock.value = true;
    return;
  }

  try {
    const data = {
      nowPW: nowPW.value,
      newPW: newPW.value,
    };

    const r = await axios.put(
      `http://localhost:8000/api/accounts/modifyPW`,
      data,
      { withCredentials: true }
    );
    if (r.status === 200) {
      className.value = "text-white";
      response.value = ["修改成功!"];
      nowPW = "";
      newPW = "";
      cNewPW = "";
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
</script>
