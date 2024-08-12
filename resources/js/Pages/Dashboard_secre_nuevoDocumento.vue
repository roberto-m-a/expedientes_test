<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout_secretaria from '@/Layouts/AuthenticatedLayout_secretaria.vue';
import vSelect from 'vue-select';


defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    departamentos: {
        type: Array,
    },
    expediente_data: {
        type: Array,
    },
    periodosEscolares: {
        type: Array,
    },
    tiposDocumentos: {
        type: Array,
    },
});

const abrir = ref(false);
const fecha = new Date();
const pesoMax = ref(false);
const fechaActual = fecha.getFullYear() + '-' + ((fecha.getMonth() + 1) < 10 ? '0' + (fecha.getMonth() + 1) : (fecha.getMonth() + 1)) + '-' + ((fecha.getDate()) < 10 ? '0' + (fecha.getDate()) : (fecha.getDate()));

const form = useForm({
    Titulo: '',
    FechaExpedicion: '',
    FechaEntrega:'',
    Departamento: '',
    TipoDocumento: '',
    PeriodoEscolar: '',
    Expediente: '',
    Archivo: '',
    Dependencia: '',
    Region: 'Interno',
    Estatus: 'En proceso'
});

const nuevoDocumento = () => {
    form.post(route('registrar.documento'), {
        
        onSuccess: () => {
            //abrir.value = false;
            form.reset()
            document.getElementById('Archivo').value = null;
            document.querySelector('#vistaPrevia').setAttribute('src', '');
        },
        onError: () => {
            console.log(form.errors);
        },
    });
};


const documentoT = async (e) => {
    //console.log('entra al metodo')
    if (!(e.target instanceof HTMLInputElement)) {
        return Promise.reject(new Error("no es HTMLInputElement"));
    }
    if(e.target.files[0] == null){
        document.getElementById('Archivo').value = null;
        document.querySelector('#vistaPrevia').setAttribute('src', '');
        form.Archivo = '';
        pesoMax.value = false;
    }
    const file = e.target.files[0];

    if (file.size > 5000000) {
        document.getElementById('Archivo').value = null;
        document.querySelector('#vistaPrevia').setAttribute('src', '');
        form.Archivo = '';
        pesoMax.value = true;
    }
    else {
        form.Archivo = file;
        let pdffFile = document.getElementById('Archivo').files[0];
        console.log(pdffFile);
        let pdffFileURL = URL.createObjectURL(pdffFile) + "#toolbar=0";
        console.log(pdffFileURL);
        document.querySelector('#vistaPrevia').setAttribute('src', pdffFileURL);
        pesoMax.value = false;
    }
    console.log(pesoMax);
};

const limpiar = () => {
    form.Archivo = '';
    document.getElementById('Archivo').value = null;
    document.querySelector('#vistaPrevia').setAttribute('src', '');
    pesoMax.value = false;
}
const controlRegion = async (e) =>{
    console.log('entra al chcked')
    if (!(e.target instanceof HTMLInputElement)) {
        return Promise.reject(new Error("no es HTMLInputElement"));
    }
    if(document.getElementById('interno').checked){
        form.Estatus = 'En proceso';
    }
    if(document.getElementById('externo').checked){
        form.Estatus = 'Entregado';
    }
}
</script>

<template>

    <Head title="Nuevo documento" />

    <AuthenticatedLayout_secretaria :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Subir documento</h2>
                <h2 class="text-gray-500 font-semibold">Recaba información de un documento para un expediente</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-2 text-center text-xl text-gray-900">Subir un nuevo documento</div>
                </div>
                <div
                    class="sm:flex sm:bg-white sm:overflow-hidden sm:shadow-sm sm:rounded-lg md:bg-white md:l-400 bg-white">
                    <div class="text-gray-900 sm:w-max sm:h-max p-6">
                        <form action="" @submit.prevent="nuevoDocumento" class="flex-col" enctype="multipart/form-data">
                            <div class="sm:flex sm:flex-row">
                                <div class="sm:flex-grow">
                                    <InputLabel for="Expediente"
                                        value="Seleccione el expediente al que desea agregar el documento"
                                        class="pt-2" />
                                    <v-select type="text" id="Expediente" label="generalInfo"
                                        placeholder="Introduce el nombre del docente al que quieras añadir el documento"
                                        :options="expediente_data" :filterable="true" v-model="form.Expediente"
                                        class="border-white" />
                                    <InputError class="mt-2" :message="form.errors.Expediente" />
                                    <InputLabel for="tipoDocumento" value="¿Qué tipo de documento es?" class="pt-2" />
                                    <v-select type="text" id="tipoDocumento" label="nombreTipoDoc"
                                        placeholder="Introduce el tipo de documento" :options="tiposDocumentos"
                                        :filterable="true" v-model="form.TipoDocumento" class="border-white" />
                                    <InputError class="mt-2" :message="form.errors.TipoDocumento" />

                                    <InputLabel for="Titulo" value="Titulo" class="pt-2" />
                                    <TextInput id="Titulo" type="text" class="mt-1 block w-full" required
                                        v-model="form.Titulo" />
                                    <InputError class="mt-2" :message="form.errors.Titulo" />
                                    <InputLabel for="FechaExpedicion" value="Fecha de expedición" class="pt-2" />
                                    <TextInput id="FechaExpedición" type="date" :max="fechaActual"
                                        class="mt-1 block w-full" required v-model="form.FechaExpedicion" />
                                    <InputError class="mt-2" :message="form.errors.FechaExpedicion" />
                                    <InputLabel for="Region" value="Region del documento" class="pt-2" />
                                    <div class=" align-middle justify-evenly space-x-2">
                                        <div class="flex flex-auto justify-evenly">
                                            <input @change="controlRegion" type="radio" id="interno" value="Interno" v-model="form.Region" />
                                            <label for="Interno">Interno</label>

                                            <input @change="controlRegion" type="radio" id="externo" value="Externo" v-model="form.Region" />
                                            <label for="Externo">Externo</label>
                                        </div>
                                        <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                            form.Region
                                        }}</div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.Region" />

                                    <div v-if="form.Region == 'Interno'">
                                        <InputLabel for="Departamento" value="Departamento" class="pt-2" />
                                        <v-select type="text" id="Departamento" label="nombreDepartamento"
                                            placeholder="Introduce el departamento del que proviene"
                                            :options="departamentos" :filterable="true" v-model="form.Departamento"
                                            class="border-white" />
                                        <InputError class="mt-2" :message="form.errors.Departamento" />
                                    </div>
                                    <div v-if="form.Region == 'Externo'">
                                        <InputLabel for="Dependencia" value="Dependencia" class="pt-2" />
                                        <TextInput id="Dependencia" type="text" class="mt-1 block w-full" required
                                            v-model="form.Dependencia" />
                                        <InputError class="mt-2" :message="form.errors.Dependencia" />
                                    </div>

                                    <div v-if="form.Region == 'Interno'" class=" align-middle justify-evenly space-x-2">
                                        <InputLabel for="Estatus" value="Estatus" class="" />
                                        <div class="flex flex-auto justify-evenly">
                                            <input type="radio" id="proceso" value="En proceso"
                                                v-model="form.Estatus" />
                                            <label for="Interno">En proceso</label>

                                            <input type="radio" id="entregado" value="Entregado"
                                                v-model="form.Estatus" />
                                            <label for="Externo">Entregado</label>
                                        </div>
                                        <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                                                    form.Estatus }}</div>
                                    </div>
                                    <div v-if="form.Estatus=='Entregado'">
                                        <InputLabel for="FechaEntrega" value="Fecha de entrega" class="pt-2" />
                                    <TextInput id="FechaEntrega" type="date" :min="form.FechaExpedicion" :max="fechaActual"
                                        class="mt-1 block w-full" required v-model="form.FechaEntrega" />
                                    <InputError class="mt-2" :message="form.errors.FechaEntrega" />
                                    </div>

                                    <InputLabel for="periodoEscolar" value="PeriodoEscolar" class="pt-2" />
                                    <v-select type="text" id="periodoEscolar" label="generalInfo"
                                        placeholder="Periodo escolar al que pertenece" :options="periodosEscolares"
                                        :filterable="true" v-model="form.PeriodoEscolar" class="border-white" />
                                    <InputError class="mt-2" :message="form.errors.PeriodoEscolar" />

                                    <InputLabel for="archivo" value="Archivo (peso maximo: 5MB)" class="pt-2" />
                                    <div class="space-y-2">
                                        <TextInput id="Archivo" type="file" class="mt-1 block w-full"
                                            accept="application/pdf" required @change="documentoT"/>
                                        <div v-if="form.Archivo != ''"
                                            class="flex flex-auto align-middle justify-center space-x-4 pr-5">
                                            <DangerButton @click="limpiar">Quitar archivo</DangerButton>
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.Archivo" />

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
                                        <embed id="vistaPrevia" type="application/pdf" width="470" height="600"
                                            class="bg-gray-700">
                                    </div>
                                    <InputLabel v-if="form.Archivo ==''" for="vistaPrevia"
                                        value="Aún no se ha subido nada" class="text-center text-l text-red-600" />
                                </div>

                            </div>

                            <div class="flex flex-col justify-center pt-2 text-center">
                                <div>
                                        <p class="text-sm p-2">
                                            *Antes de guardar el archivo asegurese de corroborar su información. Ya que
                                            en caso
                                            de que
                                            algun dato este mal tendrá que ir al apartado de expedientes y buscar el
                                            documento
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
    </AuthenticatedLayout_secretaria>
</template>
<style>
@import "vue-select/dist/vue-select.css";
</style>