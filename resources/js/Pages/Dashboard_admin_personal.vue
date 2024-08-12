<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AddButton from '@/Components/AddButton.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { ref, onMounted } from 'vue';
import vSelect from 'vue-select';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-select';
import DataTablesLib from 'datatables.net';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
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
    { data: 'Nombre' },
    { data: 'Apellidos' },
    { data: 'Sexo' },
    {
        data: null, render: function (data, type, row, meta) {
            return (data.email != null) ? data.email : `<div class=" flex justify-center space-x-1">
                <button class="CrearUsuario flex flex-items justify-center bg-green-400 hover:bg-green-600 text-black font-semibold hover:text-white py-2 px-4 border border-green-500 hover:border-transparent rounded" data-id="${row.IdPersonal}">
                    <svg class="h-5 w-5 text-black"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Crear usuario
                </button>
                </div>`;
        }
    },
    { data: 'nombreDepartamento' },
    {
        data: null, render: function (data, type, row, meta) {
            return (data.IdDocente != null) ? 'Docente' : ((data.IdAdministrador != null) ? 'Administrador' : 'Secretaria');
        }
    },

    {
        data: null, render: function (data, type, row, meta) {
            return ((data.totalDocumentos <=0 || data.totalDocumentos ==null)  && (data.numDocumentos <=0 || data.numDocumentos == null) )? `<div class=" flex justify-center space-x-1">
                <button title="Editar personal" class="EditarPersonal flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdPersonal}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    
                </button>
                <button title="borrar personal" class="BorrarPersonal flex flex-items justify-center bg-red-400 hover:bg-red-600 text-black font-semibold hover:text-white py-2 px-4 border border-red-400 hover:border-transparent rounded" data-id="${row.IdPersonal}">
                    <svg class="h-5 w-5 text-slate-900"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <line x1="4" y1="7" x2="20" y2="7" />  <line x1="10" y1="11" x2="10" y2="17" />  <line x1="14" y1="11" x2="14" y2="17" />  <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />  <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    
                </button>
                </div>` 
                : 
                `<div class=" flex justify-center space-x-1">
                <button title="Editar personal" class="EditarPersonal flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdPersonal}">
                    <svg class="h-5 w-5 text-black"  viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />  <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />  <line x1="16" y1="5" x2="19" y2="8" /></svg>
                    
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
    departamentos: {
        type: Array,
    },
    personal_data: {
        type: Array,
    }
});
const abrir = ref(false);
const crearUser = ref(false);

const form = useForm({
    Nombre: '',
    Apellidos: '',
    Sexo: 'Hombre',
    Departamento: '',
    tipoUsuario: 'Docente',
    GradoAcademico: '',
    crearUsuario: false,
    email: '',
    email_confirmation: '',
});

const nuevoPersonal = () => {
    form.put(route('personal.nuevo'), {
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
//Editar Personal
const abrirEdit = ref(false);

const formEdit = useForm({
    IdPersonal: '',
    Nombre: '',
    Apellidos: '',
    Departamento: '',
    Docente: false,
    GradoAcademico: '',
    Sexo: '',
    email: '',
    email_confirmation: '',
});
const editarPersonal = () => {
    formEdit.post(route('validar.personal'), {
        preserveScroll: true,
        onSuccess: () => {
            const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
            Swal.fire({
                title: 'Confirmación necesaria',
                text: `Para continuar, ingresa el código de confirmación: ${randomCode}`,
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
                    formEdit.put(route('personal.editar'), {
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
//Crear un usuario para un personal ya creado
const abrirAniadirUsuario = ref(false);
const formUser = useForm({
    IdPersonal: '',
    Nombre: '',
    Apellidos: '',
    email: '',
    email_confirmation: '',
});

const registrarUsuario = () => {
    formUser.post(route('validar.usuario'), {
        onSuccess: () => {
            const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
            Swal.fire({
                title: 'Confirmación necesaria',
                text: `Para continuar, ingresa el código de confirmación: ${randomCode}`,
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
                    Swal.fire('Se ha creado el usuario.', 'El código es correcto.', 'success');
                    // Aquí puedes agregar la lógica para realizar la acción deseada después de la confirmación
                    formUser.post(route('aniadir.usuario'), {
                        preserveScroll: true,
                        onSuccess: () => {
                            abrirAniadirUsuario.value = false;
                            formUser.reset()
                        },
                        onError: () => {
                            console.log(formUser.errors);
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
            console.log(formUser.errors);
        },
    })
};
//Escuchador de eventos
onMounted(() => {

    // Manejar clic en el botón de editarDepartamento
    $('#TablaPersonal').on('click', '.EditarPersonal', function () {
        abrirEdit.value = true;
        formEdit.IdPersonal = $(this).data('id');
        const personal = props.personal_data.find(a => a.IdPersonal === formEdit.IdPersonal);

        formEdit.Nombre = personal.Nombre;
        formEdit.Apellidos = personal.Apellidos;
        formEdit.Sexo = personal.Sexo;
        formEdit.Departamento = props.departamentos.find(b => b.nombreDepartamento === personal.nombreDepartamento);

        if (personal.IdDocente != null) {
            formEdit.Docente = true;
            formEdit.GradoAcademico = personal.GradoAcademico;
        } else {
            formEdit.Docente = false;
        }
        formEdit.email = personal.email;
        formEdit.email_confirmation = personal.email;
    });

    $('#TablaPersonal').on('click', '.BorrarPersonal', function () {
        formEdit.IdPersonal = $(this).data('id');
        const personal = props.personal_data.find(a => a.IdPersonal === formEdit.IdPersonal);

        const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
        Swal.fire({
            title: 'Confirmación necesaria',
            text: `¿Desea borrar a ${personal.Nombre} ${personal.Apellidos}?. 
            Para continuar, ingresa el código de confirmación: ${randomCode}`,
            input: 'number',
            footer: 'Esta acción se puede realizar ya que el usuario/personal no tiene documentos en su expediente o no ha subido algun documento.',
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
                formEdit.delete(route('personal.borrar'), {
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

    $('#TablaPersonal').on('click', '.CrearUsuario', function () {
        abrirAniadirUsuario.value = true;
        formUser.IdPersonal = $(this).data('id');
        const personal = props.personal_data.find(a => a.IdPersonal === formUser.IdPersonal);
        formUser.Nombre = personal.Nombre;
        formUser.Apellidos = personal.Apellidos;
    });
});
</script>

<template>

    <Head title="Personal" />

    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Personal</h2>
                <h2 class="text-gray-500 font-semibold">Administre a su personal y usuarios</h2>
            </div>
        </template>
        <!-- Modal para añadir un usuario a un personal ya creado -->
        <Modal :show="abrirAniadirUsuario">
            <div class="p-8 flex flex-col space-y-4">
                <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                    <DangerButton @click="abrirAniadirUsuario = false; formUser.reset();">X</DangerButton>
                </div>
                <p>
                    Agregue el correo para <span class="font-bold">{{ formUser.Nombre + ' ' + formUser.Apellidos
                        }}</span>
                </p>
                <form @submit.prevent="registrarUsuario">
                    <div class="mt-4" oncopy="return false" onpaste="return false">
                        <InputLabel for="email" value="Correo" />

                        <TextInput id="email" type="email" class="mt-1 block w-full" v-model="formUser.email"
                            required />

                        <InputError class="mt-2" :message="formUser.errors.email" />

                    </div>
                    <div class="mt-4" oncopy="return false" onpaste="return false">
                        <InputLabel for="email" value="Confirmar correo" />

                        <TextInput id="email_confirmation" type="email" class="mt-1 block w-full"
                            v-model="formUser.email_confirmation" required />

                        <InputError class="mt-2" :message="formUser.errors.email" />

                    </div>
                    <div class="flex flex-items justify-end pt-4">
                        <PrimaryButton>Guardar</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
        <!-- Modal para editar un usuario -->
        <Modal :show="abrirEdit">
            <div class="p-8 flex flex-col space-y-4">
                <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                    <DangerButton @click="abrirEdit = false; formEdit.reset();">X</DangerButton>
                </div>
                <p>
                    Edite el personal seleccionado
                </p>
                <form @submit.prevent="editarPersonal">
                    <InputLabel for="Nombre" value="Nombre(s)" class="pt-2" />
                    <TextInput id="Nombre" type="text" class="mt-1 block w-full" v-model="formEdit.Nombre" required />
                    <InputError class="mt-2" :message="formEdit.errors.Nombre" />
                    <InputLabel for="Apellidos" value="Apellido(s)" class="pt-2" />
                    <TextInput id="Apellidos" type="text" class="mt-1 block w-full" v-model="formEdit.Apellidos"
                        required />
                    <InputError class="mt-2" :message="formEdit.errors.Apellidos" />

                    <InputLabel for="Estatus" value="Sexo" />
                    <div>
                        <div class="flex flex-auto justify-evenly">
                            <label for="Hombre">Hombre</label>
                            <input type="radio" id="Hombre" value="Hombre" v-model="formEdit.Sexo" />

                            <label for="Mujer">Mujer</label>
                            <input type="radio" id="entregado" value="Mujer" v-model="formEdit.Sexo" />

                        </div>
                        <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                            formEdit.Sexo }}</div>
                    </div>
                    <InputError class="mt-2" :message="formEdit.errors.Sexo" />
                    <div>
                        <InputLabel for="Departamento" value="Departamento" class="pt-2" />
                        <v-select type="text" id="Departamento" label="nombreDepartamento"
                            placeholder="Introduce el departamento del que proviene" :options="departamentos"
                            :filterable="true" v-model="formEdit.Departamento" class="border-white" />
                        <InputError class="mt-2" :message="formEdit.errors.Departamento" />
                    </div>
                    <div v-show="formEdit.Docente">
                        <InputLabel for="" value="Grado Academico del docente" />
                        <select value="" id="GradoAcademico" v-model="formEdit.GradoAcademico"
                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="Licenciatura">Licenciatura</option>
                            <option value="Posgrado">Posgrado</option>
                            <option value="Mestria">Maestria</option>
                            <option value="Doctorado">Doctorado</option>
                            <option value="Otro">otro</option>
                        </select>
                    </div>

                    <div v-if="formEdit.email != null">
                        <div class="mt-4" oncopy="return false" onpaste="return false">
                            <InputLabel for="email" value="Correo" />

                            <TextInput id="email" type="email" class="mt-1 block w-full" v-model="formEdit.email"
                                required autocomplete="username" />

                            <InputError class="mt-2" :message="formEdit.errors.email" />

                        </div>
                        <div class="mt-4" oncopy="return false" onpaste="return false">
                            <InputLabel for="email" value="Confirmar correo" />

                            <TextInput id="email_confirmation" type="email" class="mt-1 block w-full"
                                v-model="formEdit.email_confirmation" required />

                            <InputError class="mt-2" :message="formEdit.errors.email" />

                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.gradoAcademico" />
                    <div class="flex flex-items justify-end pt-4">
                        <PrimaryButton>Guardar</PrimaryButton>
                    </div>

                </form>
            </div>
        </Modal>
        <!-- Modal para agregar un nuevo personal a plataforma -->
        <Modal :show='abrir'>
            <div class="p-8 flex flex-col space-y-4">
                <div class="flex flex-row-reverse items-end justify-between overflow-hidden">
                    <DangerButton @click="abrir = false; form.reset()">X</DangerButton>
                </div>
                <div>
                    <p>
                        Favor de rellenar los campos solicitados para agregar un nuevo personal.
                    </p>
                    <form @submit.prevent="nuevoPersonal">
                        <InputLabel for="Nombre" value="Nombre(s)" class="pt-2" />
                        <TextInput id="Nombre" type="text" class="mt-1 block w-full" v-model="form.Nombre"
                            required />
                        <InputError class="mt-2" :message="form.errors.Nombre" />
                        <InputLabel for="Apellidos" value="Apellido(s)" class="pt-2" />
                        <TextInput id="Apellidos" type="text" class="mt-1 block w-full"
                            v-model="form.Apellidos" required />
                        <InputError class="mt-2" :message="form.errors.Apellidos" />
                        <div>
                            <InputLabel for="Estatus" value="Sexo" class="" />
                            <div class="flex flex-auto justify-evenly">
                                <label for="Hombre">Hombre</label>
                                <input type="radio" id="Hombre" value="Hombre" v-model="form.Sexo" />

                                <label for="Mujer">Mujer</label>
                                <input type="radio" id="entregado" value="Mujer" v-model="form.Sexo" />

                            </div>
                            <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                form.Sexo }}</div>
                            <InputError class="mt-2" :message="form.errors.Sexo" />
                        </div>
                        <div>
                            <InputLabel for="Departamento" value="Departamento" class="" />
                            <select id="Departamento" v-model="form.Departamento"
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option v-for="departamento in departamentos"
                                    :key="departamento.IdDepartamento" :value="departamento.IdDepartamento">
                                    {{ departamento.nombreDepartamento }}
                                </option>
                            </select>
                        </div>
                        <InputError class="mt-2" :message="form.errors.Departamento" />
                        <InputLabel for="" value="Selecciona el tipo de personal" />
                        <div>
                            <div class="flex flex-auto justify-evenly">
                                <label for="Docente">Docente</label>
                                <input type="radio" id="Docente" value="Docente"
                                    v-model="form.tipoUsuario" />

                                <label for="Secretaria">Secretaria</label>
                                <input type="radio" id="Secretaria" value="Secretaria"
                                    v-model="form.tipoUsuario" />

                                <label for="Administrador">Administrador</label>
                                <input type="radio" id="Administrador" value="Administrador"
                                    v-model="form.tipoUsuario" />

                            </div>
                            <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
                                form.tipoUsuario }}</div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.Docente" />
                        <div v-if="form.tipoUsuario== 'Docente'">
                            <InputLabel for="" value="Grado Academico del docente" />
                            <select value="" id="GradoAcademico" v-model="form.GradoAcademico"
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="Licenciatura">Licenciatura</option>
                                <option value="Posgrado">Posgrado</option>
                                <option value="Mestria">Maestria</option>
                                <option value="Doctorado">Doctorado</option>
                                <option value="Otro">otro</option>
                            </select>
                        </div>
                        <div class="flex flex-auto align-middle justify-evenly p-2 space-x-2">
                            <InputLabel for="crearUser"
                                value="Marque la casilla en caso de querer crear un usuario para este personal ->" />
                            <Checkbox v-model:checked="form.crearUsuario" @click="crearUser = !crearUser;"
                                id="crearUser" value="crearUser" v-model="form.crearUsuario"
                                class="mt-1 block" />
                        </div>
                        <div v-if="form.crearUsuario">
                            <div class="mt-4" oncopy="return false" onpaste="return false">
                                <InputLabel for="email" value="Correo" />

                                <TextInput id="email" type="email" class="mt-1 block w-full"
                                    v-model="form.email" required autocomplete="username" />

                                <InputError class="mt-2" :message="form.errors.email" />

                            </div>
                            <div class="mt-4" oncopy="return false" onpaste="return false">
                                <InputLabel for="email" value="Confirmar correo" />

                                <TextInput id="email" type="email" class="mt-1 block w-full"
                                    v-model="form.email_confirmation" required autocomplete="username" />

                                <InputError class="mt-2" :message="form.errors.email" />

                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.gradoAcademico" />
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
                            Agregar nuevo personal
                        </AddButton>
                    </div>
                    <DataTable id="TablaPersonal"
                        class="w-full table-auto text-sm text-center display stripe compact cell-border order-column"
                        :options="options" :columns="columns" :data="$page.props.personal_data">
                        <thead>
                            <tr class="border-2 bg-gray-200 border-black">
                                <th
                                    class="py-2 px-4 font-bold uppercase text-l text-center border-2 border-black hover:bg-gray-300">
                                    Nombre</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-l text-center border-2 border-black hover:bg-gray-300">
                                    Apellidos</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-l text-center border-2 border-black hover:bg-gray-300">
                                    Sexo</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-l text-center border-2 border-black hover:bg-gray-300">
                                    Email</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-l text-center border-2 border-black hover:bg-gray-300">
                                    Departamento</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-l text-center border-2 border-black hover:bg-gray-300">
                                    Tipo</th>
                                <th
                                    class="py-2 px-4 font-bold uppercase text-l text-center border-2 border-black hover:bg-gray-300">
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