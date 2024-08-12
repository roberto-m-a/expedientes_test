<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import vSelect from 'vue-select';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    personalDocente: {
        type: Object,
    },
    documentosDocente: {
        type: Array,
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
    expediente: {
        type: Object,
    },
});
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-select';
import DataTablesLib from 'datatables.net';
DataTable.use(DataTablesCore);
DataTable.use(DataTablesLib);

const options = {
    order: [[0, "desc"]],
    responsive: true,
    autoWidth: true,
    language: {
        "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
    }
};

const columns = [
    { data: 'fechaExpedicion' },
    { data: 'Titulo' },
    {
        data: null, render: function (data, type, row, meta) {
            return (data.fechaEntrega == null) ?
                `En proceso: <button class="entregarDocumento flex flex-items justify-center bg-green-400 hover:bg-green-600 text-black font-semibold hover:text-white py-2 px-4 hover:border-transparent rounded" data-id="${row.IdDocumento}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />  <polyline points="22 4 12 14.01 9 11.01" /></svg>
                    Entregar
                </button>
            </div>`
                : 'Entregado el: \t' + data.fechaEntrega;
        },
    },
    { data: 'region' },
    {
        data: null, render: function (data, type, row, meta) {
            return (data.IdDepartamento == null) ? data.dependencia : data.nombreDepartamento;
        },
    },
    { data: 'nombreTipoDoc' },
    { data: 'nombre_corto' },
    {
        data: null, render: function (data, type, row, meta) {
            //const opciones;
            return `<div class=" flex justify-center space-x-1">
                    <button title="Ver documento" class="verDocumento flex flex-items justify-center bg-gray-300 hover:bg-gray-500 text-black font-semibold hover:text-white py-2 px-4 hover:border-transparent rounded" data-id="${row.URL}">
                    <svg class="h-5 w-5 text-slate-900"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />  <circle cx="12" cy="12" r="3" /></svg>
                
                </button>
                <button title="Editar documento" class="EditarDocumento flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 hover:border-transparent rounded" data-id="${row.IdDocumento}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    
                </button>
            </div>`
        },
        searchable: false,
        orderable: false,
    }
]

const fecha = new Date();
const fechaActual = fecha.getFullYear() + '-' + ((fecha.getMonth() + 1) < 10 ? '0' + (fecha.getMonth() + 1) : (fecha.getMonth() + 1)) + '-' + ((fecha.getDate()) < 10 ? '0' + (fecha.getDate()) : (fecha.getDate()));

//Editar el documento
const abrirEdit = ref(false);
const pesoMax = ref(false);
const formEdit = useForm({
    IdDocumento: '',
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
    Estatus: 'En proceso',
    URL: '',
})
//Metodo para validar y editar el documento
const editarDocumento = () => {
    formEdit.post(route('validar.documento'), {
        onSuccess: () => {
            console.log('validado correctamente')
            const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
            Swal.fire({
                title: 'Confirmación necesaria',
                text: `Esta por editar el documento ${formEdit.Titulo}. Para continuar, ingresa el código de confirmación: ${randomCode}`,
                input: 'number',
                inputAttributes: {
                    maxlength: 4,
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                preConfirm: (inputValue) => {
                    return new Promise((resolve) => {
                        if (inputValue === randomCode.toString()) {
                            resolve(true);
                        } else {
                            Swal.showValidationMessage('Código incorrecto');
                            resolve(false);
                        }
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Edición confirmada', 'El código es correcto.Espere a recargar la pagina', 'success');
                    formEdit.post(route('documento.editar'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            abrirEdit.value = false;
                            formEdit.reset();
                            document.getElementById('Archivo').value = null;
                            document.querySelector('#vistaPrevia').setAttribute('src', '');
                            location.reload();
                        },
                        onError: () => {
                            console.log(formEdit.errors);
                        },
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    abrirEdit.value = false;
                    formEdit.reset();
                    Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
                }
            });
        },
        onError: () => {
            console.log(formEdit.errors)
        }
    })
}
//Metodo para validar el archivo que se introduce en el input
const documentoT = async (e) => {

    if (!(e.target instanceof HTMLInputElement)) {
        return Promise.reject(new Error("no es HTMLInputElement"));
    }
    console.log('xd');
    if (e.target.files[0] == null) {
        document.getElementById('Archivo').value = null;
        document.querySelector('#vistaPrevia').setAttribute('src', '');
        formEdit.Archivo = '';
        pesoMax.value = false;
    }
    const file = e.target.files[0];
    console.log(file);
    if (file.size > 5000000) {
        document.getElementById('Archivo').value = null;
        document.querySelector('#vistaPrevia').setAttribute('src', '');
        formEdit.Archivo = '';
        pesoMax.value = true;
    }
    else {
        formEdit.Archivo = file;
        pesoMax.value = false;

        let pdffFile = document.getElementById('Archivo').files[0];
        let pdffFileURL = URL.createObjectURL(pdffFile) + "#toolbar=0";
        document.querySelector('#vistaPrevia').setAttribute('src', pdffFileURL);
        console.log(formEdit);
    }
};

//Entregar el documento
const abrirEntrega = ref(false);
const entregarDocumento = () => {
    formEdit.post(route('validar.entrega'), {
        onSuccess: () => {
            const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
            Swal.fire({
                title: 'Confirmación necesaria',
                text: `Esta por entregar el documento ${formEdit.Titulo}. Para continuar, ingresa el código de confirmación: ${randomCode}`,
                input: 'number',
                inputAttributes: {
                    maxlength: 4,
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                preConfirm: (inputValue) => {
                    return new Promise((resolve) => {
                        if (inputValue === randomCode.toString()) {
                            resolve(true);
                        } else {
                            Swal.showValidationMessage('Código incorrecto');
                            resolve(false);
                        }
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Entrega confirmada', 'El código es correcto.Espere a recargar la pagina', 'success');
                    formEdit.post(route('entregar.documento'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            abrirEdit.value = false;
                            formEdit.reset();
                            document.getElementById('Archivo').value = null;
                            document.querySelector('#vistaPrevia').setAttribute('src', '');
                            location.reload();
                        },
                        onError: () => {
                            console.log(formEdit.errors);
                        },
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    abrirEdit.value = false;
                    formEdit.reset();
                    Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
                }
            });
        },
        onError: () => {
            console.log(formEdit.errors);
        },
    })
}

onMounted(() => {

    // Manejar clic en el botón de editar
    $('#DocumentosDocente').on('click', '.EditarDocumento', function () {
        abrirEdit.value = true;
        formEdit.IdDocumento = $(this).data('id');
        const documento = props.documentosDocente.find(a => a.IdDocumento === formEdit.IdDocumento);
        console.log(documento);
        formEdit.Titulo = documento.Titulo;
        formEdit.FechaExpedicion = documento.fechaExpedicion;
        formEdit.FechaEntrega = documento.fechaEntrega;
        formEdit.Departamento = props.departamentos.find(a => a.IdDepartamento === documento.IdDepartamento);
        formEdit.TipoDocumento = props.tipo_documentos.find(a => a.IdTipoDocumento === documento.IdTipoDocumento);
        formEdit.PeriodoEscolar = props.periodos_escolares.find(a => a.IdPeriodoEscolar === documento.IdPeriodoEscolar);
        formEdit.Dependencia = documento.dependencia;
        formEdit.Region = documento.region;
        formEdit.Estatus = documento.Estatus;
        formEdit.URL = documento.URL;
    });

    // Manejar clic en el botón de ver
    $('#DocumentosDocente').on('click', '.verDocumento', function () {
        const urlDocumento = $(this).data('id');
        console.log(urlDocumento);
        window.open(urlDocumento, '_blank');
    });

    $('#DocumentosDocente').on('click', '.entregarDocumento', function () {
        abrirEntrega.value = true
        formEdit.IdDocumento = $(this).data('id');
        const documento = props.documentosDocente.find(a => a.IdDocumento === formEdit.IdDocumento);
        formEdit.Titulo = documento.Titulo;
        formEdit.URL = documento.URL;
    });
});

const limpiar = () => {
    formEdit.Archivo = '';
    document.getElementById('Archivo').value = null;
    document.querySelector('#vistaPrevia').setAttribute('src', '');
    pesoMax.value = false;
}
</script>

<template>
    <Head title="Expediente" />

    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Expediente de</h2>
                <h2 class="text-gray-500 font-semibold">{{ personalDocente.Nombre + ' ' +
                    personalDocente.Apellidos }}</h2>
            </div>
        </template>
        <!-- Modal para entregar el documento -->
        <Modal :show="abrirEntrega">
            <div class="p-8 flex flex-col space-y-4">
                <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                    <DangerButton @click="abrirEntrega = false; formEdit.reset()">X</DangerButton>
                </div>
                <form @submit.prevent="entregarDocumento">
                    <InputLabel for="FechaEntrega" value="Fecha de entrega" class="pt-2" />
                    <TextInput id="FechaEntrega" type="date" :min="formEdit.FechaExpedicion" :max="fechaActual"
                        class="mt-1 block w-full" required v-model="formEdit.FechaEntrega" />
                    <InputError class="mt-2" :message="formEdit.errors.FechaEntrega" />

                    <p class="font-bold text-xl text-center">Vista del documento subido</p>
                    <div class="flex justify-center">
                        <iframe :src="formEdit.URL" frameborder="0" class="w-full h-60"></iframe>
                    </div>

                    <InputLabel for="archivo"
                        value="Si desea actualizar el archivo ingrese un nuevo archivo.(peso max. 5MB)"
                        class="pt-2 text-l font-semibold text-center text-red-500" />
                    <div class="space-y-2">
                        <TextInput id="Archivo" type="file" class="mt-1 block w-full" accept="application/pdf"
                            @change="documentoT" />
                        <div v-if="formEdit.Archivo != ''"
                            class="flex flex-auto align-middle justify-center space-x-4 pr-5">
                            <DangerButton @click="limpiar">Quitar archivo</DangerButton>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="formEdit.errors.Archivo" />

                    <div v-show="pesoMax">
                        <p class="text-red-500">
                            El peso excede el permitido.
                        </p>
                    </div>

                    <div v-show="formEdit.Archivo != ''"
                        class="justify-items-center content-center p-2 text-gray-900 space-y-4">
                        <InputLabel for="vistaPrevia" value="Vista previa del nuevo documento"
                            class="text-center text-xl" />
                        <div class="flex justify-center">
                            <embed id="vistaPrevia" type="application/pdf" class="bg-gray-700 w-full h-60">
                        </div>
                        <InputLabel v-if="formEdit.Archivo == ''" for="vistaPrevia" value="Aún no se ha subido nada"
                            class="text-center text-l text-red-600" />
                    </div>

                    <div class="flex flex-col justify-center pt-2 text-center">
                        <div>
                            <p class="text-sm p-2">
                                *Antes de entregar asegurese de corroborar su información y su archivo.
                            </p>
                        </div>
                        <PrimaryButton class="flex justify-center">Entregar archivo</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
        <!-- Modal para editar el documento -->
        <Modal :show="abrirEdit">

            <div class="p-8 flex flex-col space-y-4">
                <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                    <DangerButton @click="abrirEdit = false; formEdit.reset()">X</DangerButton>
                </div>

                <form @submit.prevent="editarDocumento" class="flex-row" enctype="multipart/form-data">

                    <InputLabel for="tipoDocumento" value="¿Qué tipo de documento es?" class="pt-2" />
                    <v-select type="text" id="tipoDocumento" label="nombreTipoDoc"
                        placeholder="Introduce el tipo de documento" :options="tipo_documentos" :filterable="true"
                        v-model="formEdit.TipoDocumento" class="border-white" />
                    <InputError class="mt-2" :message="formEdit.errors.TipoDocumento" />

                    <InputLabel for="Titulo" value="Titulo" class="pt-2" />
                    <TextInput id="Titulo" type="text" class="mt-1 block w-full" required v-model="formEdit.Titulo" />
                    <InputError class="mt-2" :message="formEdit.errors.Titulo" />
                    <InputLabel for="FechaExpedicion" value="Fecha de expedición" class="pt-2" />
                    <TextInput id="FechaExpedición" type="date" :max="fechaActual" class="mt-1 block w-full" required
                        v-model="formEdit.FechaExpedicion" />
                    <InputError class="mt-2" :message="formEdit.errors.FechaExpedicion" />
                    <InputLabel for="Region" value="Region del documento" class="pt-2" />
                    <div class=" align-middle justify-evenly space-x-2">

                        <div class="flex flex-auto justify-evenly">
                            <input @change="controlRegion" type="radio" id="interno" value="Interno"
                                v-model="formEdit.Region" />
                            <label for="Interno">Interno</label>

                            <input @change="controlRegion" type="radio" id="externo" value="Externo"
                                v-model="formEdit.Region" />
                            <label for="Externo">Externo</label>
                        </div>
                        <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                            formEdit.Region
                        }}</div>
                    </div>
                    <InputError class="mt-2" :message="formEdit.errors.Region" />

                    <div v-if="formEdit.Region == 'Interno'">
                        <InputLabel for="Departamento" value="Departamento" class="pt-2" />
                        <v-select type="text" id="Departamento" label="nombreDepartamento"
                            placeholder="Introduce el departamento del que proviene" :options="departamentos"
                            :filterable="true" v-model="formEdit.Departamento" class="border-white" />
                        <InputError class="mt-2" :message="formEdit.errors.Departamento" />
                    </div>
                    <div v-if="formEdit.Region == 'Externo'">
                        <InputLabel for="Dependencia" value="Dependencia" class="pt-2" />
                        <TextInput id="Dependencia" type="text" class="mt-1 block w-full" required
                            v-model="formEdit.Dependencia" />
                        <InputError class="mt-2" :message="formEdit.errors.Dependencia" />
                    </div>

                    <div v-if="formEdit.Region == 'Interno'" class=" align-middle justify-evenly space-x-2">
                        <InputLabel for="Estatus" value="Estatus" class="" />
                        <div class="flex flex-auto justify-evenly">
                            <input type="radio" id="proceso" value="En proceso" v-model="formEdit.Estatus" />
                            <label for="Interno">En proceso</label>

                            <input type="radio" id="entregado" value="Entregado" v-model="formEdit.Estatus" />
                            <label for="Externo">Entregado</label>
                        </div>
                        <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                            formEdit.Estatus }}</div>
                    </div>
                    <div v-if="formEdit.Estatus == 'Entregado'">
                        <InputLabel for="FechaEntrega" value="Fecha de entrega" class="pt-2" />
                        <TextInput id="FechaEntrega" type="date" :min="formEdit.FechaExpedicion" :max="fechaActual"
                            class="mt-1 block w-full" required v-model="formEdit.FechaEntrega" />
                        <InputError class="mt-2" :message="formEdit.errors.FechaEntrega" />
                    </div>
                    <InputLabel for="periodoEscolar" value="PeriodoEscolar" class="pt-2" />
                    <v-select type="text" id="periodoEscolar" label="generalInfo"
                        placeholder="Periodo escolar al que pertenece" :options="periodos_escolares" :filterable="true"
                        v-model="formEdit.PeriodoEscolar" class="border-white" />
                    <InputError class="mt-2" :message="formEdit.errors.PeriodoEscolar" />

                    <p class="font-bold text-xl text-center">Vista del documento subido</p>
                    <div class="flex justify-center">
                        <iframe :src="formEdit.URL" frameborder="0" class="w-full h-60"></iframe>
                    </div>

                    <InputLabel for="archivo"
                        value="Si desea actualizar el archivo ingrese un nuevo archivo.(peso max. 5MB)"
                        class="pt-2 text-l font-semibold text-center text-red-500" />
                    <div class="space-y-2">
                        <TextInput id="Archivo" type="file" class="mt-1 block w-full" accept="application/pdf"
                            @change="documentoT" />
                        <div v-if="formEdit.Archivo != ''"
                            class="flex flex-auto align-middle justify-center space-x-4 pr-5">
                            <DangerButton @click="limpiar">Quitar archivo</DangerButton>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="formEdit.errors.Archivo" />

                    <div v-show="pesoMax">
                        <p class="text-red-500">
                            El peso excede el permitido.
                        </p>
                    </div>

                    <div v-show="formEdit.Archivo != ''"
                        class="justify-items-center content-center p-2 text-gray-900 space-y-4">
                        <InputLabel for="vistaPrevia" value="Vista previa del nuevo documento"
                            class="text-center text-xl" />
                        <div class="flex justify-center">
                            <embed id="vistaPrevia" type="application/pdf" class="bg-gray-700 w-full h-60">
                        </div>
                        <InputLabel v-if="formEdit.Archivo == ''" for="vistaPrevia" value="Aún no se ha subido nada"
                            class="text-center text-l text-red-600" />
                    </div>

                    <div class="flex flex-col justify-center pt-2 text-center">
                        <div>
                            <p class="text-sm p-2">
                                *Antes de guardar asegurese de corroborar su información.
                            </p>
                        </div>
                        <PrimaryButton class="flex justify-center">Guardar archivo</PrimaryButton>
                    </div>
                </form>
            </div>

        </Modal>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <DataTable id="DocumentosDocente"
                        class="w-full table-auto text-sm text-center display stripe compact cell-border order-column"
                        :options="options" :columns="columns" :data="$page.props.documentosDocente">
                        <thead>
                            <tr class="border-2 bg-gray-200 border-black">
                                <th
                                    class="py-2 px-4 font-bold uppercase text-sm text-center border-2 border-black hover:bg-gray-300">
                                    Expedida</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-sm text-center border-2 border-black hover:bg-gray-300">
                                    Titulo</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-sm text-center border-2 border-black hover:bg-gray-300">
                                    Estatus</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-sm text-center border-2 border-black hover:bg-gray-300">
                                    Region</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-sm text-center border-2 border-black hover:bg-gray-300">
                                    dpto./dpncia.</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-sm text-center border-2 border-black hover:bg-gray-300">
                                    TipoDoc</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-sm text-center border-2 border-black hover:bg-gray-300">
                                    Periodo</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-sm text-center border-2 border-black hover:bg-gray-300">
                                    Acciones</th>
                            </tr>
                        </thead>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout_admin>
</template>
<style>
@import "vue-select/dist/vue-select.css";
</style>