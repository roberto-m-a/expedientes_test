<script setup>
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import InputError from '@/Components/InputError.vue';
import vSelect from 'vue-select';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)
//Variable que almacena los datos que el controlador en el BACKEND retorna
const props = defineProps({
    verificarContraseña: {
        type: Boolean,
    },
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    tipo_documentos: {
        type: Array,
    },
    periodos_escolares: {
        type: Array,
    },
    departamentos: {
        type: Array,
    },
    dataHombres: {
        type: Array,
    },
    dataMujeres: {
        type: Array
    },
    labelsTipoDoc: {
        type: Array,
    },
    interpretacion: {
        type: String,
    },
});

const passwordInput = ref(null);

const formUserData = useForm({
    password: '',
    password_confirmation: '',
    Departamento: (props.personal.IdDepartamento != null) ? props.departamentos.find(b => b.IdDepartamento === props.personal.IdDepartamento) : '',
    Sexo: (props.personal.Sexo != null) ? props.personal.Sexo : 'Hombre',
});
const updatePassword = () => {
    formUserData.put(route('password.first'), {
        preserveScroll: true,
        onSuccess: () => formUserData.reset(),
        onError: () => {
            if (formUserData.errors.password) {
                formUserData.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
        },
    });
};
const show_filtros = ref(true); //Variable que maneja la vista de los filtros
//variable que permite manejar el formulario de la consulta
const form = useForm({
    Region: 'Todos',
    TipoDocumento: '',
    Departamento: '',
    Estatus: 'Todos',
    PeriodoEscolar: '',
});
//Variable que permite regresar los datasets de cada tipo de documento por sexo
let datos_grafica = computed(() => {
    return [
        {
            label: 'Hombres',
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            data: props.dataHombres,
        },
        {
            label: 'Mujeres',
            backgroundColor: 'rgba(255, 182, 193, 0.6)',
            borderColor: 'rgba(255, 182, 193, 1)',
            borderWidth: 1,
            data: props.dataMujeres,
        },
    ];
})
//Variable que permite regresar el arreglo con las cantidades de hombres y mujeres para la grafica de pastel
let dataPastel = computed(() => {
    if (props.labelsTipoDoc.length == 1) {
        return props.dataHombres.concat(props.dataMujeres);
    } else {
        return [];
    }
});
//Metodo para limpiar el formulario
const limpiarFiltros = () => {
    form.Region = 'Todos';
    form.TipoDocumento = '';
    form.Departamento = '';
    form.Estatus = 'Todos';
    form.PeriodoEscolar = '';
}
//Metodo para poder retornar la consulta con los datos enviados al formulario
const filtrarConsulta = () => {
    console.log(form);
    form.post(route('filtrar.consulta'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('si jaló')
        },
        onError: () => {
            console.log('salio error')
        }
    })
}

</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Dashboard</h2>
                <h2 class="text-gray-500 font-semibold">Información del sistema con graficas</h2>
            </div>
        </template>
        <Modal :show=verificarContraseña>
            <div class="p-8">

                <form @submit.prevent="updatePassword">
                    <p>
                        Ingresa los siguientes datos
                    </p>
                    <div>
                        <InputLabel for="Departamento" value="Departamento" class="pt-2" />
                        <v-select type="text" id="Departamento" label="nombreDepartamento"
                            placeholder="Introduce el departamento del que proviene" :options="departamentos"
                            :filterable="true" v-model="formUserData.Departamento" class="border-white" />
                        <InputError class="mt-2" :message="formUserData.errors.Departamento" />
                    </div>
                    <InputLabel for="Estatus" value="Sexo" class="" />
                    <div class="flex flex-auto justify-evenly">
                        <input type="radio" id="Hombre" value="Hombre" v-model="formUserData.Sexo" />
                        <label for="Hombre">Hombre</label>

                        <input type="radio" id="entregado" value="Mujer" v-model="formUserData.Sexo" />
                        <label for="Mujer">Mujer</label>
                    </div>
                    <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                        formUserData.Sexo }}</div>
                    <p>
                        Para continuar con tu registro, por favor ingresa una contraseña.
                    </p>
                    <p>
                        Debe de contener minimo una minuscula, una mayuscula, un numero, un simbolo y tener entre 8 y 18
                        caracteres.
                    </p>
                    <InputLabel for="password" value="Contraseña" class="pt-2" />
                    <TextInput id="password" type="password" class="mt-1 block w-full" v-model="formUserData.password"
                        required autocomplete="new-password" />
                    <InputError class="mt-2" :message="formUserData.errors.password" />
                    <InputLabel for="password-confirmation" value="Confirmar contraseña" class="pt-2" />
                    <TextInput id="password" type="password" class="mt-1 block w-full"
                        v-model="formUserData.password_confirmation" required autocomplete="new-password" />
                    <InputError class="mt-2" :message="formUserData.errors.password" />
                    <div class="flex flex-items justify-end pt-4">
                        <PrimaryButton>Guardar</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-x-2">
                    <div class="text-gray-900 text-2xl border-b-2 border-gray-400 text-center">Consultar gráfica con
                        parámetros
                    </div>

                    <div class="flex flex-row content-end justify-end pt-2">
                        <div :hidden="!show_filtros" class="space-x-2">
                            <SecondaryButton @click="limpiarFiltros">Limpiar filtros</SecondaryButton>
                            <SecondaryButton @click="show_filtros = false">Ocultar filtros</SecondaryButton>
                        </div>
                        <div :hidden="show_filtros">
                            <SecondaryButton @click="show_filtros = true">Mostrar filtros</SecondaryButton>
                        </div>
                    </div>

                    <div v-show="show_filtros">
                        <!-- Se estructura el formulario para la consulta -->
                        <form @submit.prevent="filtrarConsulta" class="space-y-2" enctype="multipart/form-data">
                            <div class="lg:flex lg:flex-row lg:space-x-2">
                                <div class="flex flex-col w-full">
                                    <div class="flex flex-row items-center">
                                        <InputLabel for="tipoDocumento" value="Por tipo de documento" class="pt-2" />
                                        <div
                                            title="Se mostrará una grafica de pastel si ingresa un tipo de documento en específico">
                                            <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <v-select type="text" id="tipoDocumento" label="nombreTipoDoc"
                                        placeholder="Introduce el tipo de documento" :options="tipo_documentos"
                                        :filterable="true" v-model="form.TipoDocumento" class="border-white" />
                                </div>
                                <div class="flex flex-col w-full">
                                    <InputLabel for="periodoEscolar" value="PeriodoEscolar" class="pt-2" />
                                    <v-select type="text" id="periodoEscolar" label="generalInfo"
                                        placeholder="Periodo escolar" :options="periodos_escolares" :filterable="true"
                                        v-model="form.PeriodoEscolar" class="border-white" />
                                </div>
                                <div class="flex flex-col w-full">
                                    <InputLabel for="Departamento" value="Por departamento" class="pt-2" />
                                    <v-select type="text" id="Departamento" label="nombreDepartamento"
                                        placeholder="Introduce el departamento" :options="departamentos"
                                        :filterable="true" v-model="form.Departamento" class="border-white" />
                                </div>
                            </div>
                            <InputLabel for="Region" value="Por región del documento" class="pt-2"
                                v-show="form.Departamento == '' || form.Departamento == null" />
                            <div class=" align-middle justify-evenly space-x-2"
                                v-show="form.Departamento == '' || form.Departamento == null">
                                <div class="flex flex-auto justify-evenly">
                                    <input type="radio" id="Todos" value="Todos" v-model="form.Region" />
                                    <label for="Todos">Todos</label>

                                    <input type="radio" id="interno" value="Interno" v-model="form.Region" />
                                    <label for="Interno">Interno</label>

                                    <input type="radio" id="externo" value="Externo" v-model="form.Region" />
                                    <label for="Externo">Externo</label>
                                </div>
                                <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                    form.Region
                                    }}</div>
                            </div>
                            <div class=" align-middle justify-evenly space-x-2">
                                <InputLabel for="Estatus" value="Por su estatus" class="" />
                                <div class="flex flex-auto justify-evenly">
                                    <input type="radio" id="Todos" value="Todos" v-model="form.Estatus" />
                                    <label for="Todos">Todos</label>

                                    <input type="radio" id="proceso" value="En proceso" v-model="form.Estatus" />
                                    <label for="En proceso">En proceso</label>

                                    <input type="radio" id="entregado" value="Entregado" v-model="form.Estatus" />
                                    <label for="Externo">Entregado</label>
                                </div>
                                <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                    form.Estatus }}</div>
                            </div>
                            <div class="flex justify-end">
                                <PrimaryButton>Consultar con filtros</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
                <!--Area de graficas-->
                <div class="md:flex md:flex-row space-x-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-x-4 content-center">
                        <div id="app">
                            <GraficaBarras :labels="props.labelsTipoDoc" :datasets="datos_grafica"
                                :text="props.interpretacion" :legend="true" />
                        </div>
                    </div>
                    <div v-if="props.labelsTipoDoc.length == 1"
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-x-4">
                        <div>
                            <GraficaPastel :data="dataPastel" />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout_admin>
</template>
<script>
//Este script permite importar los componentes de las graficas de forma correcta
import GraficaBarras from '@/Components/GraficaBarras.vue';
import GraficaPastel from '@/Components/GraficaPastel.vue';
import { computed } from 'vue';
export default {
    name: 'App',
    components: {
        GraficaBarras,
        GraficaPastel,
    },
};
</script>
<style>
 /*Se importa el estilo de los inputs que utilizan el autocompletado*/
 @import "vue-select/dist/vue-select.css";
</style>