<template>
  <nav class="flex w-[95%] mt-[2%] text-[2rem]" aria-label="Breadcrumb">
    <ol
      class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse"
    >
      <li class="inline-flex items-center">
        <a
          href="#"
          class="inline-flex items-center font-medium text-gray-700 hover:text-blue-200"
          @click.prevent="breadcrumbClick(0)"
        >
          Home
        </a>
      </li>

      <li
        v-if="hiddenBreadcrumbs.length"
        class="relative inline-flex items-center"
      >
        <div class="flex items-center">
          <svg
            class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1"
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

          <button
            @click="dropdown()"
            class="ms-1 font-medium text-gray-700 hover:text-blue-200 md:ms-2"
          >
            ...
          </button>

          <ul
            v-show="dropdownOpen"
            class="absolute max-h-[50vh] hide-scrollbar overflow-x-auto overflow-y-auto w-fit top-full left-0 mt-2 bg-gray-300 border border-blue-300 rounded-[1rem] shadow-md z-10"
            @mouseleave="dropdownOpen = false"
          >
            <li
              v-for="(item, index) in hiddenBreadcrumbs"
              :key="index"
              class="px-4 py-2 hover:bg-blue-200 hover:rounded-[1rem] cursor-pointer whitespace-nowrap"
              @click="breadcrumbClick(index + 1)"
            >
              {{ item.name }}
            </li>
          </ul>
        </div>
      </li>

      <li
        v-for="(item, index) in visibleEndBreadcrumbs"
        :key="index"
        aria-current="page"
      >
        <div class="flex items-center">
          <svg
            class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1"
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
          <a
            href="#"
            class="ms-1 font-medium text-gray-700 hover:text-blue-200 md:ms-2"
            @click.prevent="
              breadcrumbClick(
                breadcrumb.length - visibleEndBreadcrumbs.length + index
              )
            "
          >
            {{ item.name }}
          </a>
        </div>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { Base64 } from "js-base64";

const props = defineProps(["PATH"]);

const router = useRouter();
const dropdownOpen = ref(false);

const breadcrumb = computed(() => {
  const splitPath = props.PATH.split("-");
  return splitPath.map((p, index) => {
    return {
      name: p,
      path:
        "/fileList/" +
        Base64.encodeURI(splitPath.slice(0, index + 1).join("-")),
    };
  });
});

const hiddenBreadcrumbs = computed(() => {
  localStorage.setItem(
    "previousPath",
    breadcrumb.value[breadcrumb.value.length - 1]["path"]
  );

  if (breadcrumb.value.length <= 5) return [];
  return breadcrumb.value.slice(1, breadcrumb.value.length - 3);
});

const visibleEndBreadcrumbs = computed(() => {
  if (breadcrumb.value.length <= 5) return breadcrumb.value.slice(1);
  return breadcrumb.value.slice(breadcrumb.value.length - 3);
});

function dropdown() {
  dropdownOpen.value = !dropdownOpen.value;
}

function breadcrumbClick(index) {
  const newBreadcrumb = breadcrumb.value.slice(0, index + 1);
  const path = newBreadcrumb[newBreadcrumb.length - 1].path;
  dropdownOpen.value = false;

  router.push(path);
}
</script>
