<script setup>
import AddButton from '@/Components/AddButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref, onMounted } from 'vue';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-select';
import DataTablesLib from 'datatables.net';
import AuthenticatedLayout_secretaria from '@/Layouts/AuthenticatedLayout_secretaria.vue';
DataTable.use(DataTablesCore);
DataTable.use(DataTablesLib);

const options = {
    select: false,
    responsive: true,
    autoWidth: true,
    language: {
        "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
    }
};

const columns = [
    { data: 'nombreTipoDoc' },
    {
        data: null, render: function (data, type, row, meta) {
            return (data.numDocumentos == 0) ? `<div class=" flex justify-center space-x-1">
                <button class="EditarTipoDoc flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdTipoDocumento}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    Editar
                </button>
                <button class="BorrarTipoDoc flex flex-items justify-center bg-red-400 hover:bg-red-600 text-black font-semibold hover:text-white py-2 px-4 border border-red-400 hover:border-transparent rounded" data-id="${row.IdTipoDocumento}">
                    <svg class="h-5 w-5 text-slate-900"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <line x1="4" y1="7" x2="20" y2="7" />  <line x1="10" y1="11" x2="10" y2="17" />  <line x1="14" y1="11" x2="14" y2="17" />  <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />  <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    Borrar
                </button>
                </div>`:
                `<div class=" flex justify-center space-x-1">
                <button class="EditarTipoDoc flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdTipoDocumento}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    Editar
                </button>
                </div>`;
        },
        searchable: false,
        orderable: false,
    }
]

const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    tipoDocs: {
        type: Object,
    }
});

const abrir = ref(false);
console.log(abrir);

const form = useForm({
    nombreTipoDoc: '',
});

const nuevoTipoDoc = () => {
    form.put(route('tipoDoc.nuevo'), {
        preserveScroll: true,
        onSuccess: () => {
            abrir.value = false;
            form.reset()
        },
        onError: () => {
            console.log(form.errors);
        },
    });
};
//Editar documentos
const abrirEdit = ref(false);
const formEdit = useForm({
    idtipoDoc: '',
    nombreTipoDoc: '',
});

const editTipoDoc = () => {
    formEdit.post(route('validar.tipoDoc'), {
        preserveScroll: true,
        onSuccess: () => {
            const TipoDoc = props.tipoDocs.find(a => a.IdTipoDocumento === formEdit.idtipoDoc);
            let registros = TipoDoc.numDocumentos;
            const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
            Swal.fire({
                title: 'Confirmación necesaria',
                text: `Esta accion afectará a ${registros} registros. Para continuar, ingresa el código de confirmación: ${randomCode}`,
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
                    Swal.fire('Edición confirmada', 'El código es correcto.', 'success');
                    // Aquí puedes agregar la lógica para realizar la acción deseada después de la confirmación
                    formEdit.put(route('tipoDoc.editar'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            abrirEdit.value = false;
                            formEdit.reset()
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
            console.log('no pasó')
            console.log(formEdit.errors);
        },
    });
};

onMounted(() => {

    // Manejar clic en el botón de editarDoc
    $('#TablaTipoDoc').on('click', '.EditarTipoDoc', function () {
        abrirEdit.value = true;
        formEdit.idtipoDoc = $(this).data('id');

        const TipoDoc = props.tipoDocs.find(a => a.IdTipoDocumento === formEdit.idtipoDoc);
        console.log(TipoDoc);
        formEdit.nombreTipoDoc = TipoDoc.nombreTipoDoc;
        console.log(formEdit);
    });
    //Manejar el clic del boton de BorrartipoDoc
    $('#TablaTipoDoc').on('click', '.BorrarTipoDoc', function () {
        formEdit.idtipoDoc = $(this).data('id');

        const TipoDoc = props.tipoDocs.find(a => a.IdTipoDocumento === formEdit.idtipoDoc);
        formEdit.nombreTipoDoc = TipoDoc.nombreTipoDoc;

        const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
        Swal.fire({
            title: 'Confirmación necesaria',
            text: `¿Desea borrar ${TipoDoc.nombreTipoDoc}?. 
            Para continuar, ingresa el código de confirmación: ${randomCode}`,
            input: 'number',
            footer: 'Esta acción se puede realizar ya que el registro no tiene relaciones con otros registros.',
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
                Swal.fire('Borrado realizado', 'El código es correcto.', 'success');
                // Aquí puedes agregar la lógica para realizar la acción deseada después de la confirmación
                formEdit.delete(route('tipoDoc.borrar'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        formEdit.reset()
                    },
                    onError: () => {
                        console.log(formEdit.errors);
                    },
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
            }
        });
    });

});
</script>

<template>

    <Head title="Tipo de documentos" />

    <AuthenticatedLayout_secretaria :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Tipos de documentos</h2>
                <h2 class="text-gray-500 font-semibold">Agrega, edita y borra tipos de documentos</h2>
            </div>
        </template>
        <Modal :show='abrirEdit' class="">
            <div class="p-8 flex flex-col space-y-4">
                <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                    <DangerButton @click="abrirEdit = false; formEdit.errors.nombreTipoDoc = ''">X</DangerButton>
                </div>
                <div>
                    <p>
                        Edita el tipo de documento
                    </p>
                    <form @submit.prevent="editTipoDoc">
                        <InputLabel for="tipoDoc" value="Nombre del tipo de Documento" class="pt-2" />
                        <TextInput id="tipoDoc" type="text" class="mt-1 block w-full" v-model="formEdit.nombreTipoDoc"
                            autofocus required />
                        <InputError class="mt-2" :message="formEdit.errors.nombreTipoDoc" />
                        <div class="flex flex-items justify-end pt-4">
                            <PrimaryButton>Guardar</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>
        <!-- Mofal para agregar un nuevo tipo de documento -->
        <Modal :show='abrir'>
            <div class="p-8 flex flex-col space-y-4">
                <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                    <DangerButton @click="abrir = false">X</DangerButton>
                </div>
                <div>
                    <p>
                        Favor de rellenar el unico campo visible para registrar un nuevo tipo de documento.
                    </p>
                    <form @submit.prevent="nuevoTipoDoc">
                        <InputLabel for="tipoDoc" value="Nombre del tipo de Documento" class="pt-2" />
                        <TextInput id="tipoDoc" type="text" class="mt-1 block w-full"
                            v-model="form.nombreTipoDoc" autofocus required />
                        <InputError class="mt-2" :message="form.errors.nombreTipoDoc" />
                        <div class="flex flex-items justify-end pt-4">
                            <PrimaryButton>Guardar</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 space-y-3">
                    <div class="flex flex-auto justify-end">
                        <AddButton @click="abrir = true">
                            Agregar nuevo tipo de documento
                        </AddButton>
                    </div>
                    <DataTable id="TablaTipoDoc"
                        class="w-full table-auto text-sm text-center display stripe compact cell-border order-column"
                        :options="options" :columns="columns" :data="$page.props.tipoDocs">
                        <thead>
                            <tr class="border-2 bg-gray-200 border-black">
                                <th
                                    class="py-2 px-4 font-bold uppercase text-xl text-center border-2 border-black hover:bg-gray-300">
                                    Nombre</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-xl text-center border-2 border-black hover:bg-gray-300">
                                    Acciones</th>
                            </tr>
                        </thead>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout_secretaria>
</template>
