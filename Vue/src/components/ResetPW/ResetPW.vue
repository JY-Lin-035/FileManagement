<template>
  <div class="flex items-center justify-center bg-gray-500">
    <Notice :inputShow="inputShow" :notices="response" :showMode="showMode" :className="className"
      @close="showMode = false, lock = true" />
      
    <div class="w-[370px] [perspective:10000px] transition-all duration-[800ms] ease-in-out"
    :class="IN ? 'opacity-100':'opacity-0'">
      <div
        class="relative flex flex-col items-center justify-center w-full h-full gap-4 p-6 bg-white shadow-md rounded-xl">
        <h2 class="text-xl font-bold">重設密碼</h2>

        <input type="email" placeholder="電子信箱" v-model="email"
          class="w-full px-3 py-2 border rounded mt-[10px]" :class="(valEmail || !email) ? '' : 'border-red-500'" />
        <p v-if="!valEmail && email != ''" class="text-red-500 text-[12px]">
          *請輸入正確的 Email 格式
        </p>

        <input type="password" placeholder="新密碼" v-model="PW" class="w-full px-3 py-2 border rounded"
          :class="(valPW || !PW) ? '' : 'border-red-500'" />
        <p v-if="!valPW && PW != ''" class="text-red-500 text-[12px]">
          *密碼須包含英文大小寫、數字以及至少 3 種不同符號<br>*長度為 12 到 100 字元之間
        </p>

        <input type="password" placeholder="確認密碼" v-model="CPW" class="w-full px-3 py-2 border rounded"
          :class="valCPW ? '' : 'border-red-500'" />
        <p v-if="!valCPW != ''" class="text-red-500 text-[12px]">
          與密碼不相符
        </p>


        <span class="flex justify-between w-full mt-[20px]">
          <input type="text" placeholder="輸入驗證碼" v-model="code" class="px-3 py-2 border rounded"
            :class="valCode ? '' : 'border-red-500'" />

          <button @click="getCode()" class="px-4 py-2 text-white "
            :disabled="!(valEmail) || waitCode" :class="waitCode ? 'bg-gray-500 rounded':'bg-blue-500 rounded'">{{ waitCode ? '傳送驗證碼(' + waitTime + ')':'傳送驗證碼' }}</button>
        </span>

        <p v-if="!valCode" class="text-red-500 text-[12px]">
          驗證碼錯誤
        </p>

        <button @click="resetPW()" class="bg-blue-500 text-white px-4 py-2 rounded w-full mt-[25px]"
          :disabled="!(PW && lock && code && valEmail)">送出</button>
          <button @click="back()" class="mt-2 text-blue-500" :disabled="!lock">返回</button>
      </div>
    </div>
  </div>

</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';

import axios from 'axios';

import Notice from '../Notices.vue';
import { onMounted } from 'vue';



async function checkSession() {
    try {
      const r = await axios.get(`http://localhost:8000/api/accounts/checkSession`, { withCredentials: true });
      if (r.status === 200) {
        var PATH = localStorage.getItem('previousPath');
        if (!PATH) {
          PATH = '/fileList'
        }
        router.push(PATH);
      } else {
        localStorage.clear();
        localStorage.setItem('previousPath', '/');
      };
    } catch (e) {
      localStorage.clear();
      localStorage.setItem('previousPath', '/');
      console.log(e.response)
    };
};

const IN = ref(false);
onMounted(() => {
  checkSession();

  setTimeout(() => {
    IN.value = true;
  }, 1);
});



const email = ref('');
const PW = ref('');
const CPW = ref('');
const code = ref('');
const valCode = ref(true);

const response = ref('');
const showMode = ref(false);
const inputShow = ref(false);
const className = ref('')
const lock = ref(true);

const clearInput = () => {
  email.value = '';
  PW.value = '';
  CPW.value = '';
  code.value = '';
  response.value = '';  
  showMode.value = false;
  inputShow.value = false;
  className.value = ''
  lock.value = true;
};


const valEmail = computed(
  () => /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email.value)
);

const valPW = computed(() => {
  const value = PW.value
  const upper = /[A-Z]/.test(value)
  const lower = /[a-z]/.test(value)
  const digit = /\d/.test(value)
  const symbol = value.match(/[^A-Za-z0-9]/g) || []
  const symbols = new Set(symbol).size >= 3
  const long = value.length >= 12 && value.length <= 100

  return upper && lower && digit && symbols && long;
});

const valCPW = computed(() => PW.value === CPW.value);


// TODO: getCode
const waitCode = ref(false);
let waitTime = ref(30);

async function getCode() {
  lock.value = false;

  if (email.value === '') {
    className.value = 'text-red-500';
    response.value = ['電子信箱不能為空!'];
    showMode.value = true;
    lock.value = true;
    return;
  }

  try {
    const data = {
      mode: 'pw',
      email: email.value,
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

    const r = await axios.post(`http://localhost:8000/api/accounts/getCode`, data, { withCredentials: true });
    console.log(r.data.status);
    if (r.status === 200) {
      className.value = 'text-white';
      response.value = [r.data.message];
    } else {
      response.value = ['傳送失敗，請檢查信箱地址。'];
      className.value = 'text-white';
    }
    showMode.value = true;
  } catch (e) {
    if (e.response && e.response.status >= 400 && e.response.status < 500 && e.response.data) {
      response.value = Object.values(e.response.data).flat();
    } else {
      response.value = Object.values(e.response.data.errors).flat();
    }

    className.value = 'text-red-500';
    showMode.value = true;
  }
  lock.value = true;
}

async function resetPW() {
  lock.value = false;

  if (PW.value === '' || code.value === '') {
    className.value = 'text-red-500';
    response.value = ['密碼及驗證碼不能為空!'];
    showMode.value = true;
    lock.value = true;
    return;
  }

  try {
    const data = {
      email: email.value,
      password: PW.value,
      code: code.value,
    };
    
    const r = await axios.put(`http://localhost:8000/api/accounts/resetPW`, data, { withCredentials: true });
    console.log(r.data.status);
    if (r.status === 200) {
      back();
    } else {
      response.value = ['重設失敗, 請稍後再試。'];
      className.value = 'text-white';
    }
    showMode.value = true;
  } catch (e) {
    if (e.response && e.response.status >= 400 && e.response.status < 500 && e.response.data) {
      response.value = Object.values(e.response.data).flat();
    } else {
      response.value = Object.values(e.response.data.errors).flat();
    }

    className.value = 'text-red-500';
    showMode.value = true;
  }
  lock.value = true;
}


const router = useRouter();
const route = useRoute();

function back(){
  var PATH = localStorage.getItem('previousPath');
  if (!PATH) {
    PATH = '/'
  }
  
  clearInput();
  router.push(PATH);
}
</script>
