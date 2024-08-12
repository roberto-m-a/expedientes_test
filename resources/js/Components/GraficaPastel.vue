<template>
  <div class="chart-container">
    <canvas ref="canvas"></canvas>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default {
  name: 'PieChart',
  props: {
    data: {
      type: Array,
      required: true
    },
  },
  setup(props) {
    const canvas = ref(null);
    let chartInstance = null;

    const createChart = () => {
      if (chartInstance) {
        chartInstance.destroy();
      }
      chartInstance = new Chart(canvas.value, {
        type: 'pie',
        data: {
          labels: ['Hombres', 'Mujeres'],
          datasets: [
            {
              data: props.data,
              backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 182, 193, 0.6)'],
            },
          ],
        },
        options: {
          plugins:{
            legend: {
            position: 'bottom',
          },
          },
          responsive: true,
          maintainAspectRatio: false,
        },
      });
    };

    onMounted(() => {
      createChart();
    });

    watch(
      () => [props.data, props.labels],
      () => {
        createChart();
      }
    );

    return {
      canvas,
    };
  },
};
</script>

<style scoped>
.chart-container {
    position: relative;
    width: 100%;
    height: 100%;
  }
  
  canvas {
    width: 100% !important;
    height: 100% !important;
  }
</style>