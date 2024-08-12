<script setup>
import AddButton from '@/Components/AddButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, onMounted } from 'vue';
import InputError from '@/Components/InputError.vue';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-select';
import DataTablesLib from 'datatables.net';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
DataTable.use(DataTablesCore);
DataTable.use(DataTablesLib);

const options = {
    order: [[0, "desc"]],
    select: true,
    responsive: true,
    autoWidth: true,
    language: {
        "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
    }
};

const columns = [
    { data: 'fechaInicio' },
    { data: 'fechaTermino' },
    { data: 'nombre_corto' },
    {
        data: null, render: function (data, type, row, meta) {
            return (data.numDocumentos == 0) ?
                `<div class=" flex justify-center space-x-1">
                <button class="EditarPeriodo flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdPeriodoEscolar}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    Editar
                </button>
                <button class="BorrarPeriodo flex flex-items justify-center bg-red-400 hover:bg-red-600 text-black font-semibold hover:text-white py-2 px-4 border border-red-400 hover:border-transparent rounded" data-id="${row.IdPeriodoEscolar}">
                    <svg class="h-5 w-5 text-slate-900"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <line x1="4" y1="7" x2="20" y2="7" />  <line x1="10" y1="11" x2="10" y2="17" />  <line x1="14" y1="11" x2="14" y2="17" />  <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />  <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    Borrar
                </button>
                </div>`
                :
                `<div class=" flex justify-center space-x-1">
                <button class="EditarPeriodo flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdPeriodoEscolar}">
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
    periodosEscolares: {
        type: Array,
    },
});

const abrir = ref(false);

const fecha = new Date();
const fechaActual = fecha.getFullYear() + '-' + ((fecha.getMonth() + 1) < 10 ? '0' + (fecha.getMonth() + 1) : (fecha.getMonth() + 1)) + '-' + ((fecha.getDate()) < 10 ? '0' + (fecha.getDate()) : (fecha.getDate()));
console.log(abrir);

const form = useForm({
    fechaInicio: '',
    fechaTermino: '',
    nombre_corto: '',
});

const nuevoPeriodoEscolar = () => {
    form.put(route('periodoEscolar.nuevo'), {
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

//Editar un periodo escolar

const editarPeriodoEscolar = () => {
    formEdit.post(route('validar.periodoEscolar'), {
        preserveScroll: true,
        onSuccess: () => {
            const periodoEscolar = props.periodosEscolares.find(a => a.IdPeriodoEscolar === formEdit.IdPeriodoEscolar);
            let registros = periodoEscolar.numDocumentos;
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
                    formEdit.put(route('periodoEscolar.editar'), {
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
            console.log(formEdit.errors);
        },
    });
};
const formEdit = useForm({
    IdPeriodoEscolar: '',
    fechaInicio: '',
    fechaTermino: '',
    nombre_corto: '',
});

const abrirEdit = ref(false);


onMounted(() => {

    // Manejar clic en el botón de editarDoc
    $('#TablaPeriodos').on('click', '.EditarPeriodo', function () {
        abrirEdit.value = true;
        formEdit.IdPeriodoEscolar = $(this).data('id');
        const periodoEscolar = props.periodosEscolares.find(a => a.IdPeriodoEscolar === formEdit.IdPeriodoEscolar);
        formEdit.fechaInicio = periodoEscolar.fechaInicio;
        formEdit.fechaTermino = periodoEscolar.fechaTermino;
        formEdit.nombre_corto = periodoEscolar.nombre_corto;
    });
    $('#TablaPeriodos').on('click', '.BorrarPeriodo', function () {
        formEdit.IdPeriodoEscolar = $(this).data('id');
        const periodoEscolar = props.periodosEscolares.find(a => a.IdPeriodoEscolar === formEdit.IdPeriodoEscolar);
        formEdit.fechaInicio = periodoEscolar.fechaInicio;
        formEdit.fechaTermino = periodoEscolar.fechaTermino;
        formEdit.nombre_corto = periodoEscolar.nombre_corto;

        const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
        Swal.fire({
            title: 'Confirmación necesaria',
            text: `¿Desea borrar ${periodoEscolar.nombre_corto}?. 
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
                formEdit.delete(route('periodoEscolar.borrar'), {
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

    <Head title="Periodos Escolares" />

    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Periodos escolares</h2>
                <h2 class="text-gray-500 font-semibold">Agrega, edita y borra periodos escolares</h2>
            </div>
        </template>

        <Modal :show='abrirEdit'>
            <div class="p-8 flex flex-col space-y-4">
                <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                    <DangerButton @click="abrirEdit = false">X</DangerButton>
                </div>
                <div>
                    <p>
                        Edita los datos del periodo escolar seleccionado
                    </p>
                    <form @submit.prevent="editarPeriodoEscolar">
                        <div class="space-y-2">
                            <InputLabel for="fechaInicio" value="fecha de inicio" class="" />
                            <TextInput id="fechaInicio" type="date" class="block w-full" v-model="formEdit.fechaInicio"
                                required />
                            <InputError class="mt-2" :message="formEdit.errors.fechaInicio" />
                            <InputLabel for="fechaTermino" value="Fecha de termino" class="" />
                            <TextInput id="fechaTermino" type="date" class="block w-full"
                                v-model="formEdit.fechaTermino" required />
                            <InputError class="mt-2" :message="formEdit.errors.fechaTermino" />
                        </div>
                        <InputLabel for="nombre_corto" value="Nombre corto" class="pt-2" />
                        <TextInput id="nombre_corto" type="text" class="mt-1 block w-full"
                            v-model="formEdit.nombre_corto" autofocus required />
                        <InputError class="mt-2" :message="formEdit.errors.nombre_corto" />
                        <div class="flex flex-items justify-end pt-4">
                            <PrimaryButton>Guardar</PrimaryButton>
                        </div>
                    </form>

                </div>
            </div>
        </Modal>
        <!-- Modal para agregar un nuevo periodo escolar -->
        <Modal :show='abrir'>
            <div class="p-8 flex flex-col space-y-4">
                <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                    <DangerButton @click="abrir = false">X</DangerButton>
                </div>
                <div>
                    <p>
                        Favor de rellenar los campos para registrar un nuevo periodo escolar.
                    </p>
                    <form @submit.prevent="nuevoPeriodoEscolar">
                        <div class="space-y-2">
                            <InputLabel for="fechaInicio" value="fecha de inicio" class="" />
                            <TextInput id="fechaInicio" type="date" class="block w-full"
                                v-model="form.fechaInicio" required />
                            <InputError class="mt-2" :message="form.errors.fechaInicio" />
                            <InputLabel for="fechaTermino" value="Fecha de termino" class="" />
                            <TextInput id="fechaTermino" type="date" class="block w-full"
                                v-model="form.fechaTermino" required />
                            <InputError class="mt-2" :message="form.errors.fechaTermino" />
                        </div>
                        <InputLabel for="nombre_corto" value="Nombre corto" class="pt-2" />
                        <TextInput id="nombre_corto" type="text" class="mt-1 block w-full"
                            v-model="form.nombre_corto" autofocus required />
                        <InputError class="mt-2" :message="form.errors.nombre_corto" />
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
                            Agregar nuevo periodo escolar
                        </AddButton>
                    </div>
                    <DataTable id="TablaPeriodos"
                        class="w-full table-auto text-sm text-center display stripe compact cell-border order-column"
                        :options="options" :columns="columns" :data="$page.props.periodosEscolares">
                        <thead>
                            <tr class="border-2 bg-gray-200 border-black">
                                <th
                                    class="py-2 px-4 font-bold uppercase text-xl text-center border-2 border-black hover:bg-gray-300">
                                    Fecha Inicio</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-xl text-center border-2 border-black hover:bg-gray-300">
                                    Fecha Termino</th>
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
    </AuthenticatedLayout_admin>
</template>
