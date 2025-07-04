<template>
  <div class="flex items-center justify-center">
    <Notice
      :notices="response"
      :className="className"
      :showMode="showMode"
      @close="(showMode = false), (lock = true)"
    />

    <div
      class="w-[20vw] max-w-[370px] min-w-[250px] transition-all duration-[800ms] ease-in-out [perspective:10000px]"
      :class="
        flipped ? 'h-[570px]' : 'h-[420px]', IN ? 'opacity-100' : 'opacity-0'
      "
    >
      <div
        class="w-full h-full transition-transform duration-[800ms] [transform-style:preserve-3d]"
        :class="flipped ? '[transform:rotateY(-180deg)]' : ''"
      >
        <Login
          @flip="flipped = true"
          @notice="
            (res, cn) => {
              className = cn;
              response = res;
              showMode = true;
            }
          "
        />

        <Register
          @flip="flipped = false"
          @notice="
            (res, cn) => {
              className = cn;
              response = res;
              showMode = true;
            }
          "
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { Base64 } from "js-base64";

import axios from "axios";

import Login from "./Login.vue";
import Register from "./Register.vue";
import Notice from "../Notices.vue";
import { onMounted } from "vue";

const router = useRouter();

const flipped = ref(false);
const className = ref("");
const showMode = ref(false);

async function checkSession() {
  try {
    const r = await axios.get(
      `http://localhost:8000/api/accounts/checkSession`,
      { withCredentials: true }
    );
    if (r.status === 200) {
      var PATH = localStorage.getItem("previousPath");
      if (!PATH) {
        PATH = `/fileList/${Base64.encodeURI("Home")}`;
      }
      router.push(PATH);
    } else {
      localStorage.clear();
      localStorage.setItem("previousPath", "/");
    }
  } catch (e) {
    localStorage.clear();
    localStorage.setItem("previousPath", "/");
    console.log(e.response);
  }
}

const IN = ref(false);
onMounted(() => {
  checkSession();

  setTimeout(() => {
    IN.value = true;
  }, 1);
});
</script>
