<script setup>
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import vSelect from 'vue-select';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-select';
import DataTablesLib from 'datatables.net';
DataTable.use(DataTablesCore);
DataTable.use(DataTablesLib);

const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    documentos: {
        type: Array,
    },
    periodos_escolares: {
        type: Array,
    },
    departamentos: {
        type: Array,
    },
    tipo_documentos: {
        type: Array,
    },
    expedientes: {
        type: Array,
    },
});
//para filtrar la tabla
const show_filtros = ref(true);
const fecha = new Date();
const fechaActual = fecha.getFullYear() + '-' + ((fecha.getMonth() + 1) < 10 ? '0' + (fecha.getMonth() + 1) : (fecha.getMonth() + 1)) + '-' + ((fecha.getDate()) < 10 ? '0' + (fecha.getDate()) : (fecha.getDate()));


const form = useForm({
    DptoDpencia: '',
    TipoDocumento: '',
    PeriodoEscolar: '',
    Region: 'Todos',
    Estatus: 'Todos',
});

//Agregacion de la busqueda en orden alfabetico
var _alphabetSearch;
//Funcion que permite retornar los registros que cumplen con el filtro alfabetico
$.fn.dataTable.ext.search.push(function (settings, searchData) {
    if (!_alphabetSearch) { // No search term - all results shown
        return true;
    }

    if (searchData[3].charAt(0) === _alphabetSearch) {
        return true;
    }

    return false;
});
//Funcion que permite inicializar la tabla
$(document).ready(function () {
    var table = $('#tablaDocs').DataTable({
        order: [[0, "desc"]],
        paging: true,
        searching: true,
        responsive: true,
        autoWidth: false,
        language: {
            "decimal": "",
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
            "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
            "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ Entradas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            },
        },
        data: props.documentos,
        columns: [
            { title: "Expedida", data: 'fechaExpedicion'},
            {
                title: "Estatus", data: null, render: function (data, type, row, meta) {
                    return (data.fechaEntrega == null) ?
                        `En proceso: <button class="entregarDocumento flex flex-items justify-center bg-green-400 hover:bg-green-600 text-black font-semibold hover:text-white py-2 px-4 hover:border-transparent rounded" data-id="${row.IdDocumento}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />  <polyline points="22 4 12 14.01 9 11.01" /></svg>
                    Entregar
                </button>
            </div>`
                        : 'Entregado el: \t' + data.fechaEntrega;
                },
            },
            { title: "Nombre", data: 'Nombre' },
            { title: "Apellidos", data: 'Apellidos' },
            { title: "Titulo", data: 'Titulo' },
            { title: 'Region', data: 'region' },
            {
                title: 'Dpto/Dpncia', data: null, render: function (data, type, row, meta) {
                    return (data.IdDepartamento == null) ? data.dependencia : data.nombreDepartamento;
                },
            },
            { title: 'TipoDoc', data: 'nombreTipoDoc' },
            { title: 'Periodo', data: 'nombre_corto' },
            {
                title: "Acciones", data: null, render: function (data, type, row, meta) {
                    return `<div class=" flex justify-center space-x-1">
                    <button title="Ver documento" class="verDocumento flex flex-items justify-center bg-gray-300 hover:bg-gray-500 text-black font-semibold hover:text-white py-2 px-4 hover:border-transparent rounded" data-id="${row.URL}">
                    <svg class="h-5 w-5 text-slate-900"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />  <circle cx="12" cy="12" r="3" /></svg>
                
                </button>
                <button title="Editar documento" class="EditarDocumento flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 hover:border-transparent rounded" data-id="${row.IdDocumento}">
                    <svg class="h-5 w-5 text-black" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    
                </button>
            </div>`;
                },
                searchable: false,
                orderable: false,
            }
        ],
    });
    //Filtros que retornan los registros que cumplen con el valor ingresado en cada input
    //filtros estatus
    $("#ipt_todos_estatus").change(function () {
        table.column($(this).data('index')).search('').draw();
    });

    $("#ipt_proceso").change(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });

    $("#ipt_entregado").change(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });
    //filtros region
    $("#ipt_todos_region").change(function () {
        table.column($(this).data('index')).search('').draw();
    });

    $("#ipt_interno").change(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });

    $("#ipt_externo").change(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });
    //filtro tipo de documento
    $("#tipoDocumento").keyup(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });
    //filtro departamento o dependencia
    $("#inpt_dpto_dpencia").keyup(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });
    //filtro por periodo
    $("#inpt_periodo").keyup(function () {
        table.column($(this).data('index')).search(this.value).draw();
    });
    //Filtro para las fechas de expedicion
    $("#FechaExpedicion_ini").keyup(function () {
        table.draw();
    });
    //Accionar el boton para limpiar los filtros
    $("#btn_limpiarFiltros").click(function () {
        form.reset();
        table.columns().search(this.value).draw();

        alphabet.find('.active').removeClass('active');
        $("#clear").addClass('active');

        _alphabetSearch = $(this).data('letter');
        table.draw();
    });

    var alphabet = $('<div class="alphabet"/>').append('Apellido: ');

    $('<span class="clear active"/>')
        .data('letter', '')
        .html('Todos')
        .appendTo(alphabet);

    for (var i = 0; i < 26; i++) {
        var letter = String.fromCharCode(65 + i);

        $('<span/>')
            .data('letter', letter)
            .html(letter)
            .appendTo(alphabet);
    }

    alphabet.insertBefore(table.table().container());

    alphabet.on('click', 'span', function () {
        alphabet.find('.active').removeClass('active');
        $(this).addClass('active');

        _alphabetSearch = $(this).data('letter');
        table.draw();
    });

});

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
    Expediente: '',
    Archivo: '',
    Dependencia: '',
    Region: 'Interno',
    Estatus: 'En proceso',
    URL: '',
})

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
//Entregar archivo
const abrirEntrega = ref(false);

const entregarDocumento = () => {
    formEdit.post(route('validar.entrega'), {
        onSuccess: () =>{
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
//Metodo para manejar los botones
onMounted(() => {

    // Manejar clic en el botón de ver
    $('#tablaDocs').on('click', '.verDocumento', function () {
        const urlDocumento = $(this).data('id');
        window.open(urlDocumento, '_blank');
    });
    //Manejar clic del boton de entrega
    $('#tablaDocs').on('click', '.entregarDocumento', function () {
        abrirEntrega.value = true
        formEdit.IdDocumento = $(this).data('id');
        const documento = props.documentos.find(a => a.IdDocumento === formEdit.IdDocumento);
        formEdit.FechaExpedicion = documento.fechaExpedicion;
        formEdit.Titulo = documento.Titulo;
        formEdit.URL = documento.URL;
    });
    //Manejar el clic del boton de editar
    $('#tablaDocs').on('click', '.EditarDocumento', function () {
        abrirEdit.value = true;
        formEdit.IdDocumento = $(this).data('id');
        const documento = props.documentos.find(a => a.IdDocumento === formEdit.IdDocumento);
        console.log(documento);
        formEdit.Titulo = documento.Titulo;
        formEdit.FechaExpedicion = documento.fechaExpedicion;
        formEdit.FechaEntrega = documento.fechaEntrega;
        formEdit.Departamento = props.departamentos.find(a => a.IdDepartamento === documento.IdDepartamento);
        formEdit.TipoDocumento = props.tipo_documentos.find(a => a.IdTipoDocumento === documento.IdTipoDocumento);
        formEdit.PeriodoEscolar = props.periodos_escolares.find(a => a.IdPeriodoEscolar === documento.IdPeriodoEscolar);
        formEdit.Expediente = props.expedientes.find(a => a.IdExpediente === documento.IdExpediente);
        formEdit.Dependencia = documento.dependencia;
        formEdit.Region = documento.region;
        formEdit.Estatus = documento.Estatus;
        formEdit.URL = documento.URL;
    });
});
//Metodo para limpiar el input del archivo
const limpiar = () => {
    formEdit.Archivo = '';
    document.getElementById('Archivo').value = null;
    document.querySelector('#vistaPrevia').setAttribute('src', '');
    pesoMax.value = false;
}
</script>

<template>

    <Head title="Documentos" />

    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Documentos</h2>
                <h2 class="text-gray-500 font-semibold">Ve y edita documentos</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-2">
                <!-- Modal para entregar el documento -->
                <Modal :show="abrirEntrega">
                    <div class="p-8 flex flex-col space-y-4">
                        <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                            <DangerButton @click="abrirEntrega = false; formEdit.reset()">X</DangerButton>
                        </div>
                        <form @submit.prevent="entregarDocumento">
                            <InputLabel for="FechaEntrega" value="Fecha de expedición" class="pt-2" />
                            <InputLabel for="FechaEntrega" :value="formEdit.FechaExpedicion" class="pt-2 font-semibold text-xl" />
                            <InputLabel for="FechaEntrega" value="Fecha de entrega" class="pt-2" />
                                <TextInput id="FechaEntrega" type="date" :min="formEdit.FechaExpedicion"
                                    :max="fechaActual" class="mt-1 block w-full" required
                                    v-model="formEdit.FechaEntrega" />
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
                                <InputLabel v-if="formEdit.Archivo == ''" for="vistaPrevia"
                                    value="Aún no se ha subido nada" class="text-center text-l text-red-600" />
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

                            <InputLabel for="Expediente"
                                value="Seleccione el expediente al que desea agregar el documento" class="pt-2" />
                            <v-select type="text" id="Expediente" label="generalInfo"
                                placeholder="Introduce el nombre del docente al que quieras añadir el documento"
                                :options="expedientes" :filterable="true" v-model="formEdit.Expediente"
                                class="border-white" />
                            <InputError class="mt-2" :message="formEdit.errors.Expediente" />

                            <InputLabel for="tipoDocumento" value="¿Qué tipo de documento es?" class="pt-2" />
                            <v-select type="text" id="tipoDocumento" label="nombreTipoDoc"
                                placeholder="Introduce el tipo de documento" :options="tipo_documentos"
                                :filterable="true" v-model="formEdit.TipoDocumento" class="border-white" />
                            <InputError class="mt-2" :message="formEdit.errors.TipoDocumento" />

                            <InputLabel for="Titulo" value="Titulo" class="pt-2" />
                            <TextInput id="Titulo" type="text" class="mt-1 block w-full" required
                                v-model="formEdit.Titulo" />
                            <InputError class="mt-2" :message="formEdit.errors.Titulo" />
                            <InputLabel for="FechaExpedicion" value="Fecha de expedición" class="pt-2" />
                            <TextInput id="FechaExpedición" type="date" :max="fechaActual" class="mt-1 block w-full"
                                required v-model="formEdit.FechaExpedicion" />
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
                                <TextInput id="FechaEntrega" type="date" :min="formEdit.FechaExpedicion"
                                    :max="fechaActual" class="mt-1 block w-full" required
                                    v-model="formEdit.FechaEntrega" />
                                <InputError class="mt-2" :message="formEdit.errors.FechaEntrega" />
                            </div>
                            <InputLabel for="periodoEscolar" value="PeriodoEscolar" class="pt-2" />
                            <v-select type="text" id="periodoEscolar" label="generalInfo"
                                placeholder="Periodo escolar al que pertenece" :options="periodos_escolares"
                                :filterable="true" v-model="formEdit.PeriodoEscolar" class="border-white" />
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
                                <InputLabel v-if="formEdit.Archivo == ''" for="vistaPrevia"
                                    value="Aún no se ha subido nada" class="text-center text-l text-red-600" />
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
                <!-- Contenido de la pagina -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-y-2">

                    <div class="text-gray-900 text-xl border-b-2 border-gray-400 text-center">Parametros para filtrar
                    </div>
                    <div class="flex flex-row content-end justify-end">
                        <div :hidden="!show_filtros" class="space-x-2">
                            <SecondaryButton id="btn_limpiarFiltros">Limpiar filtros</SecondaryButton>
                            <SecondaryButton @click="show_filtros = false">Ocultar filtros</SecondaryButton>
                        </div>
                        <div :hidden="show_filtros">
                            <SecondaryButton @click="show_filtros = true">Mostrar filtros</SecondaryButton>
                        </div>
                    </div>

                    <div v-show="show_filtros" class="flex flex-col space-x-1">
                        <div class="lg:flex lg:flex-row lg:space-x-2">
                            <div class="flex flex-col w-full">
                                <InputLabel for="tipoDocumento" value="Tipo de documento" class="pt-2" />
                                <TextInput type="text" placeholder="Escribe el tipo de documento" id="tipoDocumento"
                                    data-index="7" class="w-full" v-model="form.TipoDocumento" />
                            </div>
                            <div class="flex flex-col w-full">
                                <InputLabel for="Departamento" value="Departamento o Dependencia" class="pt-2" />
                                <TextInput type="text" placeholder="Escribe el departamento o dependencia"
                                    id="inpt_dpto_dpencia" data-index="6" class="w-full" v-model="form.DptoDpencia" />
                            </div>
                            <div class="flex flex-col w-full">
                                <InputLabel for="PeriodoEscolar" value="Periodo Escolar" class="pt-2" />
                                <TextInput type="text" placeholder="Escribe el periodo. Ejemplo: 'ENE-JUN 2024'"
                                    id="inpt_periodo" data-index="8" class="w-full" v-model="form.PeriodoEscolar" />
                            </div>
                        </div>
                        <div class=" align-middle justify-evenly space-x-2">
                            <InputLabel for="Estatus" value="Estatus" class="" />
                            <div class="flex flex-auto justify-evenly">
                                <label for="Todos">Todos</label>
                                <input type="radio" id="ipt_todos_estatus" value="Todos" v-model="form.Estatus"
                                    data-index="1" />
                                <label for="Interno">En proceso</label>
                                <input type="radio" id="ipt_proceso" value="En proceso" v-model="form.Estatus"
                                    data-index="1" />
                                <label for="Externo">Entregado</label>
                                <input type="radio" id="ipt_entregado" value="Entregado" v-model="form.Estatus"
                                    data-index="1" />

                            </div>
                            <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                form.Estatus }}</div>
                        </div>

                        <InputLabel for="Region" value="Region del documento" class="pt-2" />
                        <div class=" align-middle justify-evenly space-x-2">

                            <div class="flex flex-auto justify-evenly">
                                <label for="Todos">Todos</label>
                                <input type="radio" id="ipt_todos_region" value="Todos" v-model="form.Region"
                                    data-index="5" />

                                <label for="Interno">Interno</label>
                                <input type="radio" id="ipt_interno" value="Interno" v-model="form.Region"
                                    data-index="5" />

                                <label for="Externo">Externo</label>
                                <input type="radio" id="ipt_externo" value="Externo" v-model="form.Region"
                                    data-index="5" />

                            </div>
                            <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                form.Region
                            }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-y-2">
                    <table id="tablaDocs" class="md:w-min md:l-min">
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout_admin>
</template>
<style>
@import "vue-select/dist/vue-select.css";
</style>
<style>
div.alphabet {
    display: table;
    width: 100%;
    margin-bottom: 1em;
}

div.alphabet span {
    display: table-cell;
    color: #3174c7;
    cursor: pointer;
    text-align: center;
    width: 3.5%
}

div.alphabet span:hover {
    text-decoration: underline;
}

div.alphabet span.active {
    color: black;
}

#tablaDocs {
    font-size: 14px;
}

#tablaDocs thead {
    background-color: lightgray;
}

#tablaDocs thead tr th {
    border: 2px solid black;
    text-align: center;
}

#tablaDocs tbody tr td {
    border: 1px solid lightgrey;
    text-align: center;
}
</style>