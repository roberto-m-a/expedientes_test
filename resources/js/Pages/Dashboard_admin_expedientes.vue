<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-responsive';
import 'datatables.net-select';
import DataTablesLib from 'datatables.net';
import AuthenticatedLayout_admin from '@/Layouts/AuthenticatedLayout_admin.vue';
import { ref, onMounted } from 'vue';
DataTable.use(DataTablesCore);
DataTable.use(DataTablesLib);

const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    expedientes: {
        type: Array,
    },
});

//Funcion para poder redirigir a un expediente en especifico
const formExpediente = useForm({

});
onMounted(() => {
    // Manejar clic en el botón de abrirExpediente
    $('#tablaExpedientes').on('click', '.abrirExpediente', function () {
        const idExpediente = $(this).data('id');
        console.log(idExpediente);
        var ruta = 'expediente/' + idExpediente;
        formExpediente.get(route('expediente.especifico', { id: idExpediente }));
    });
});
//Agregacion de la busqueda en orden alfabetico
var _alphabetSearch;

$.fn.dataTable.ext.search.push(function (settings, searchData) {
    if (!_alphabetSearch) { // No search term - all results shown
        return true;
    }

    if (searchData[2].charAt(0) === _alphabetSearch) {
        return true;
    }

    return false;
});

$(document).ready(function () {
    var table = $('#tablaExpedientes').DataTable({
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
            },},
            data: props.expedientes,
            columns: [
                { title: "# de docs", data: 'numDocumentos' },
                { title: "Nombre", data: 'Nombre' },
                { title: "Apellidos", data: 'Apellidos' },
                { title: "Departamento", data: 'nombreDepartamento' },
                {
                    title: "Acciones", data: null, render: function (data, type, row, meta) {
                        return `<div class=" flex justify-center">
                <button class="abrirExpediente flex flex-items justify-center bg-blue-400 hover:bg-blue-800 text-black font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" data-id="${row.IdExpediente}">
                <svg class="h-5 w-5 text-slate-900"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                </svg>
                Abrir
                </button>
                
                </div>`;
                    },
                    searchable: false,
                    orderable: false,
                }
            ],
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

</script>
<template>

    <Head title="Expedientes" />

    <AuthenticatedLayout_admin :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Expedientes</h2>
                <h2 class="text-gray-500 font-semibold">Consulta los expedientes</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <table id="tablaExpedientes"> </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout_admin>
</template>
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

#tablaExpedientes thead{
    background-color: lightgray;
}
#tablaExpedientes thead tr th{
    border: 2px solid black;
    text-align: center;
}
#tablaExpedientes tbody tr td{
    border: 1px solid lightgrey;
    text-align: center;
}
</style>