<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import vSelect from 'vue-select';
//informacion cargada desde la base de datos
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
    expediente: {
        type: Object,
    },
    periodosEscolares: {
        type: Array,
    },
    tiposDocumentos: {
        type: Array,
    },
});
const fecha = new Date();
const pesoMax = ref(false);
//variable para obtener la fecha actual con el formato utilizado
const fechaActual = fecha.getFullYear() + '-' + ((fecha.getMonth() + 1) < 10 ? '0' + (fecha.getMonth() + 1) : (fecha.getMonth() + 1)) + '-' + ((fecha.getDate()) < 10 ? '0' + (fecha.getDate()) : (fecha.getDate()));
//formulario para el documento
const formDocumento = useForm({
    Titulo: '',
    FechaExpedicion: '',
    FechaEntrega: '',
    Departamento: '',
    TipoDocumento: '',
    PeriodoEscolar: '',
    Expediente: props.expediente,
    Archivo: '',
    Dependencia: '',
    Region: 'Interno',
    Estatus: 'En proceso'
});

//metodo que permite registrar un documento nuevo en la base de datos
const nuevoDocumento = () => {
    formDocumento.post(route('registrar.documento'), {
        onSuccess: () => {
            //abrir.value = false;
            formDocumento.reset();
            document.getElementById('Archivo').value = null;
            document.querySelector('#vistaPrevia').setAttribute('src', '');
        },
        onError: () => {
            console.log(formDocumento.errors);
        },
    });
};
//Metodo para escuchar el cambio del input del archivo y validarla para poder mostrar una previsualizacion
const documentoT = async (e) => {
    console.log('entra al metodo')

    if (!(e.target instanceof HTMLInputElement)) {
        return Promise.reject(new Error("no es HTMLInputElement"));
    }
    if (e.target.files[0] == null) {
        document.getElementById('Archivo').value = null;
        document.querySelector('#vistaPrevia').setAttribute('src', '');
        formDocumento.Archivo = '';
        pesoMax.value = false;
    }
    const file = e.target.files[0];

    if (file.size > 5000000) {
        document.getElementById('Archivo').value = null;
        document.querySelector('#vistaPrevia').setAttribute('src', '');
        formDocumento.Archivo = '';
        pesoMax.value = true;
    }
    else {
        formDocumento.Archivo = file;
        let pdffFile = document.getElementById('Archivo').files[0];
        let pdffFileURL = URL.createObjectURL(pdffFile) + "#toolbar=0";
        document.querySelector('#vistaPrevia').setAttribute('src', pdffFileURL);
        pesoMax.value = false;
    }
};
//metodo para quitar el archivo del input
const limpiar = () => {
    formDocumento.Archivo = '';
    document.getElementById('Archivo').value = null;
    document.querySelector('#vistaPrevia').setAttribute('src', '');
}
//metodo para controlar la region y el valor del estatus
const controlRegion = async (e) =>{
    console.log('entra al chcked')
    if (!(e.target instanceof HTMLInputElement)) {
        return Promise.reject(new Error("no es HTMLInputElement"));
    }
    if(document.getElementById('interno').checked){
        formDocumento.Estatus = 'En proceso';
    }
    if(document.getElementById('externo').checked){
        formDocumento.Estatus = 'Entregado';
    }
}
</script>

<template>

    <Head title="Subir documento" />

    <AuthenticatedLayout :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Subir documento</h2>
                <h2 class="text-gray-500 font-semibold">Recaba informacion para un documento nuevo en tu expediente</h2>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-2">
                <div
                    class="sm:flex sm:bg-white sm:overflow-hidden sm:shadow-sm sm:rounded-lg md:bg-white md:l-400 bg-white">
                    <div class="text-gray-900 sm:w-max sm:h-max p-6">
                        <!-- Estructura del para el formulario del documento -->
                        <form action="" @submit.prevent="nuevoDocumento" class="space-y-2">
                            <div class="sm:flex sm:flex-row">
                                <div class="sm:flex-grow">
                                    <InputLabel for="tipoDocumento" value="¿Qué tipo de documento es?" class="pt-2" />
                                    <v-select type="text" id="tipoDocumento" label="nombreTipoDoc"
                                        placeholder="Introduce el tipo de documento" :options="tiposDocumentos"
                                        :filterable="true" v-model="formDocumento.TipoDocumento" class="border-white" />
                                    <InputError class="mt-2" :message="formDocumento.errors.TipoDocumento" />

                                    <InputLabel for="Titulo" value="Titulo" class="pt-2" />
                                    <TextInput id="Titulo" type="text" class="mt-1 block w-full" required
                                        v-model="formDocumento.Titulo" />
                                    <InputError class="mt-2" :message="formDocumento.errors.Titulo" />
                                    <InputLabel for="FechaExpedicion" value="Fecha de expedición" class="pt-2" />
                                    <TextInput id="FechaExpedición" type="date" :max="fechaActual"
                                        class="mt-1 block w-full" required v-model="formDocumento.FechaExpedicion" />
                                    <InputError class="mt-2" :message="formDocumento.errors.FechaExpedicion" />
                                    <InputLabel for="Region" value="Region del documento" class="pt-2" />
                                    <div class=" align-middle justify-evenly space-x-2">

                                        <div class="flex flex-auto justify-evenly">
                                            <input @change="controlRegion" type="radio" id="interno" value="Interno"
                                                v-model="formDocumento.Region" />
                                            <label for="Interno">Interno</label>

                                            <input @change="controlRegion" type="radio" id="externo" value="Externo"
                                                v-model="formDocumento.Region" />
                                            <label for="Externo">Externo</label>
                                        </div>
                                        <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
        formDocumento.Region }}</div>
                                    </div>
                                    <InputError class="mt-2" :message="formDocumento.errors.Region" />

                                    <div v-if="formDocumento.Region == 'Interno'">
                                        <InputLabel for="Departamento" value="Departamento" class="pt-2" />
                                        <v-select type="text" id="Departamento" label="nombreDepartamento"
                                            placeholder="Introduce el departamento del que proviene"
                                            :options="departamentos" :filterable="true"
                                            v-model="formDocumento.Departamento" class="border-white" />
                                        <InputError class="mt-2" :message="formDocumento.errors.Departamento" />
                                    </div>
                                    <div v-if="formDocumento.Region == 'Externo'">
                                        <InputLabel for="Dependencia" value="Dependencia" class="pt-2" />
                                        <TextInput id="Dependencia" type="text" class="mt-1 block w-full" required
                                            v-model="formDocumento.Dependencia" />
                                        <InputError class="mt-2" :message="formDocumento.errors.Dependencia" />
                                    </div>
                                    <div v-if="formDocumento.Region == 'Interno'"
                                        class=" align-middle justify-evenly space-x-2">
                                        <InputLabel for="Estatus" value="Estatus" class="" />
                                        <div class="flex flex-auto justify-evenly">
                                            <input type="radio" id="proceso" value="En proceso"
                                                v-model="formDocumento.Estatus" />
                                            <label for="Interno">En proceso</label>

                                            <input type="radio" id="entregado" value="Entregado"
                                                v-model="formDocumento.Estatus" />
                                            <label for="Externo">Entregado</label>
                                        </div>
                                        <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
        formDocumento.Estatus }}</div>
                                    </div>
                                    <div v-if="formDocumento.Estatus=='Entregado'">
                                        <InputLabel for="FechaEntrega" value="Fecha de entrega" class="pt-2" />
                                    <TextInput id="FechaEntrega" type="date" :min="formDocumento.FechaExpedicion" :max="fechaActual"
                                        class="mt-1 block w-full" required v-model="formDocumento.FechaEntrega" />
                                    <InputError class="mt-2" :message="formDocumento.errors.FechaEntrega" />
                                    </div>

                                    <InputLabel for="periodoEscolar" value="PeriodoEscolar" class="pt-2" />
                                    <v-select type="text" id="periodoEscolar" label="generalInfo"
                                        placeholder="Periodo escolar al que pertenece" :options="periodosEscolares"
                                        :filterable="true" v-model="formDocumento.PeriodoEscolar"
                                        class="border-white" />
                                    <InputError class="mt-2" :message="formDocumento.errors.PeriodoEscolar" />

                                    <InputLabel for="archivo" value="Archivo (peso maximo: 5MB)" class="pt-2" />
                                    <div class="space-y-2">
                                        <TextInput id="Archivo" type="file" class="mt-1 block w-full"
                                            accept="application/pdf" required @change="documentoT" />
                                        <div v-if="formDocumento.Archivo != ''"
                                            class="flex flex-auto align-middle justify-center space-x-4 pr-5">
                                            <DangerButton @click="limpiar">Quitar archivo</DangerButton>
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="formDocumento.errors.Archivo" />

                                    <div v-show="pesoMax">
                                        <p class="text-red-500">
                                            El peso excede el permitido.
                                        </p>
                                    </div>
                                </div>
                                <div class="justify-items-center content-center p-2 text-gray-900 space-y-4">
                                    <InputLabel for="vistaPrevia" value="Vista previa del documento"
                                        class="text-center text-xl" />
                                        <div class="flex justify-center">
                                            <embed id="vistaPrevia" type="application/pdf" width="500" height="600"
                                        class="bg-gray-700">
                                        </div>
                                    <InputLabel v-if="formDocumento.Archivo ==''" for="vistaPrevia"
                                        value="Aún no se ha subido nada" class="text-center text-l text-red-600" />
                                </div>

                            </div>
                            
                            <div class="flex flex-col justify-center pt-2 text-center">
                                <div>
                                <p class="text-sm p-2">
                                    *Antes de guardar el archivo asegurese de corroborar su información. Ya que en caso
                                    de que
                                    algun dato este mal tendrá que ir al apartado de expedientes y buscar el documento
                                    entre
                                    todos los expedientes y editarlo desde ahi.
                                </p>
                            </div>
                                <PrimaryButton class="flex justify-center">Guardar archivo</PrimaryButton>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style>
@import "vue-select/dist/vue-select.css";
</style>