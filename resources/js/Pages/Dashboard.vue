<script setup>
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import vSelect from 'vue-select';
import { ref } from 'vue';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)
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
    departamentos: {
        type: Array,
    },
    documentos_data: {
        type: Array,
    },
    interpretacion: {
        type: String,
    }
});
const passwordInput = ref(null);

const formDataUser = useForm({
    password: '',
    password_confirmation: '',
    Departamento: (props.personal.IdDepartamento != null) ? props.departamentos.find(b => b.IdDepartamento === props.personal.IdDepartamento) : '',
    Sexo: (props.personal.Sexo != null) ? props.personal.Sexo : 'Hombre',
});
const updatePassword = () => {
    formDataUser.put(route('password.first'), {
        preserveScroll: true,
        onSuccess: () => formDataUser.reset(),
        onError: () => {
            if (formDataUser.errors.password) {
                formDataUser.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
        },
    });
};

//Para las graficas
let cantidades = [];
let labels = [];
props.documentos_data.forEach((tipo_documento) => {
    cantidades.push(tipo_documento.cantidad);
    labels.push(tipo_documento.nombreTipoDoc);
})
const dataset = [
    {
        label: 'cantidad',
        backgroundColor: 'rgba(75, 192, 192, 0.6)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1,
        data: cantidades,
    },]
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Dashboard</h2>
                <h2 class="text-gray-500 font-semibold">Documentos en tu expediente</h2>
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
                            :filterable="true" v-model="formDataUser.Departamento" class="border-white" />
                        <InputError class="mt-2" :message="formDataUser.errors.Departamento" />
                    </div>
                    <InputLabel for="Estatus" value="Sexo" class="" />
                    <div class="flex flex-auto justify-evenly">
                        <input type="radio" id="Hombre" value="Hombre" v-model="formDataUser.Sexo" />
                        <label for="Hombre">Hombre</label>

                        <input type="radio" id="entregado" value="Mujer" v-model="formDataUser.Sexo" />
                        <label for="Mujer">Mujer</label>
                    </div>
                    <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                        formDataUser.Sexo }}</div>
                    <p>
                        Para continuar con tu registro, por favor ingresa una contraseña.
                    </p>
                    <p>
                        Debe de contener minimo una minuscula, una mayuscula, un numero, un simbolo y tener entre 8 y 18
                        caracteres.
                    </p>
                    <InputLabel for="password" value="Contraseña" class="pt-2" />
                    <TextInput id="password" type="password" class="mt-1 block w-full" v-model="formDataUser.password"
                        required autocomplete="new-password" />
                    <InputError class="mt-2" :message="formDataUser.errors.password" />
                    <InputLabel for="password-confirmation" value="Confirmar contraseña" class="pt-2" />
                    <TextInput id="password" type="password" class="mt-1 block w-full"
                        v-model="formDataUser.password_confirmation" required autocomplete="new-password" />
                    <InputError class="mt-2" :message="formDataUser.errors.password" />
                    <div class="flex flex-items justify-end pt-4">
                        <PrimaryButton>Guardar</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!--Area de graficas-->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-x-2">
                    <div id="app">
                        <GraficaBarras :labels="labels" :datasets="dataset" :text="props.interpretacion" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script>
//Este script permite importar los componentes de las graficas de forma correcta
import GraficaBarras from '@/Components/GraficaBarras.vue';
import { forEach } from 'jszip';

export default {
    name: 'App',
    components: {
        GraficaBarras,
    },
};
</script>
<style>
@import "vue-select/dist/vue-select.css";
</style>