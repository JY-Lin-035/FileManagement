<template>
  <div class="py-6" id="donut-chart"></div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useStorage } from "../../stores/storage";

const router = useRouter();
const route = useRoute();
const storage = useStorage();

const signal = computed(() => storage.format(storage.signalStorage));
const used = computed(() => storage.format(storage.usedStorage));
const usedGB = computed(() => storage.format(storage.usedStorage, "GB"));
const available = computed(() => storage.format(storage.availableStorage));
const availableGB = computed(() =>
  storage.format(storage.availableStorage, "GB")
);
const total = computed(() => storage.format(storage.totalStorage));
const percentage = computed(() => storage.percentage);

let chart = null;

const getChartOptions = () => {
  return {
    series: [usedGB.value[0], availableGB.value[0]],
    colors: ["#E74694", "#16BDCA"],
    chart: {
      height: "100%",
      width: "100%",
      type: "donut",
      animations: {
        enabled: true,
        easing: "easeinout",
        speed: 350,
        animateGradually: {
          enabled: true,
          delay: 100,
        },
      },
    },
    states: {
      active: {
        allowMultipleDataPointsSelection: false,
        filter: {
          type: "none",
        },
      },
    },
    stroke: {
      colors: ["transparent"],
      lineCap: "",
    },
    plotOptions: {
      pie: {
        donut: {
          labels: {
            show: true,
            name: {
              show: true,
              fontFamily: "Inter, sans-serif",
              fontSize: "100%",
              color: "white",
              offsetY: 100,
            },
            total: {
              showAlways: true,
              show: true,
              label: "",
              fontFamily: "Inter, sans-serif",
              fontSize: "100%",
              color: "white",
              fontWeight: "bold",
              formatter: function (w) {
                return `${Number(percentage.value)} %`;
              },
            },
            value: {
              show: true,
              fontFamily: "Inter, sans-serif",
              fontSize: "100%",
              color: "white",
              offsetY: 0,
              formatter: function (w) {
                return `${Number(percentage.value)} %`;
              },
            },
          },
          size: "70%",
        },
      },
    },
    grid: {
      padding: {
        top: -2,
      },
    },
    tooltip: {
      y: {
        formatter: function (val) {
          val =
            val === usedGB.value[0]
              ? [used.value[0], used.value[1]]
              : [available.value[0], available.value[1]];
          return `${val[0]} ${val[1]}`;
        },
        title: {
          formatter: function () {
            return "";
          },
        },
      },
    },
    labels: [
      `${used.value[0]} ${used.value[1]} Used`,
      `${available.value[0]} ${available.value[1]} Available`,
    ],
    dataLabels: {
      enabled: false,
    },
    legend: {
      position: "bottom",
      fontFamily: "Inter, sans-serif",
      fontSize: "15%",
    },
    yaxis: {
      labels: {
        formatter: function (value) {
          return ``;
        },
      },
    },
    xaxis: {
      labels: {
        formatter: function (value) {
          return value;
        },
      },
      axisTicks: {
        show: false,
      },
      axisBorder: {
        show: false,
      },
    },
  };
};

const updateChart = () => {
  if (chart) {
    chart.updateOptions(getChartOptions());
  }
};

watch([used, total], () => {
  updateChart();
});

onMounted(() => {
  const data = storage.loadFromLocalStorage();

  if (!data) {
    storage.getFromAPI();
  }

  if (
    document.getElementById("donut-chart") &&
    typeof ApexCharts !== "undefined"
  ) {
    chart = new ApexCharts(
      document.getElementById("donut-chart"),
      getChartOptions()
    );
    chart.render();
  }
});
</script>

<style>
.apexcharts-legend-text {
  color: white !important;
  margin-left: -10px !important;
  margin-right: 15px !important;
}
</style>
