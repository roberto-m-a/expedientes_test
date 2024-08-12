<template>
    <div class="flex flex-items place-items-center">
      <img :src="imagenURL" alt="Imagen" width="55"/>
    </div>
</template>

<script>
export default {
  data() {
    return {
      imagenURL: ''
    };
  },
  mounted() {
    // Importa dinámicamente la imagen
    import('@/assets/logo_ito.png')
      .then(url => {
        this.imagenURL = url.default;
      })
      .catch(error => {
        console.error('Error al cargar la imagen:', error);
      });
  }
};
</script>