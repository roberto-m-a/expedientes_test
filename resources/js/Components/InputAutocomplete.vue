<template>
    <div>
        <input 
        type="text" 
        v-model="seleccion" 
        @input="change"
        @keydown.down="down"
        @keydown.up="up"
        @keydown.enter="enter"
        >
  
        <ul class="dropdown-menu" v-if="mostrar">
          <li @click="click(index)"
              :class="{ 'active': isActive(index) || encima }" 
              v-for="(item, index) in resultados" 
              :key="index">{{ item }}</li>
        </ul>
  
    </div>
  </template>
  
  <script>
  export default {
    
    props: ['items'],
  
    data() {
  
      return {
        encima: false,
        actual: 0,
        mostrar: false,
        seleccion: ""
      }
  
    },
  
    computed: {
  
      resultados() {
        return this.items.filter(elem => {
            return elem.toLowerCase().includes(this.seleccion.toLowerCase());
        });
      }
  
    },
  
    methods: {
  
      isActive(index) {
        return index == this.actual;
      },
  
      down() {
        if(this.actual < this.resultados.length) {
          this.actual++;
        }
  
        console.log(this.actual, this.resultados.length);
      },
  
      up() {
        if(this.actual > 0) {
          this.actual--;
        }
  
        console.log(this.actual, this.resultados.length);
      },
  
      enter() {
        this.$emit('selecionado', this.resultados[this.actual]);
  
        // Cerramos el area de selección
        this.mostrar = false;
      },
  
      click(index) {
        this.$emit('selecionado', this.resultados[index]);
  
        // Cerramos el area de selección
        this.mostrar = false;
      },
  
      change() {
        if(!this.mostrar) {
          this.mostrar = true;
        }
      }
  
    }
  
  }
  </script>
  
  <style>
    
    .dropdown-menu .active {
      text-decoration: underline;
    }
  
    .dropdown-menu li:hover, .dropdown-menu li:focus {
      text-decoration: underline;
    }
  
  </style>