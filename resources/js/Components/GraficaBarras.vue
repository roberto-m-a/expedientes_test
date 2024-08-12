<template>
    <div>
        <canvas ref="barChart"></canvas>
    </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default {
    name: 'BarChart',
    props: {
        labels: {
            type: Array,
            required: true,
        },
        datasets: {
            type: Array,
            required: true,
        },
        text: {
            type: String,
            required: true,
        },
        legend:{
            type: Boolean,
            required: false,
        }
    },
    setup(props) {
        const barChart = ref(null);
        let chartInstance = null;

        const createChart = () => {
            const ctx = barChart.value.getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: props.labels,
                    datasets: props.datasets,
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            display: props.legend,
                        },
                        title: {
                            display: true,
                            text: props.text,
                            font: {
                                size: 20, // Tamaño del texto
                            },
                        },
                    },
                    scales: {
                        x: {
                            stacked: true,
                        },
                        y: {
                            stacked: true,
                        },
                    },
                },
            });
        };

        onMounted(() => {
            createChart();
        });

        watch(
            () => [props.labels, props.datasets],
            () => {
                if (chartInstance) {
                    chartInstance.destroy();
                }
                createChart();
            },
            { deep: true }
        );

        return {
            barChart,
        };
    },
};
</script>

<style scoped>
.chart-container {
    width: 100%;
    height: 100%;
    position: relative;
}

canvas {
    width: 100% !important;
    height: 100% !important;
}
</style>