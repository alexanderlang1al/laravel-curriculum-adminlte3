<template>
    <div>
        <v-swatches
            :swatch-size="swatchSize"
            :trigger-style="triggerStyle"
            :popover-to="popoverTo"
            v-model="content"
            @input="handleInput"
            :max-height="height"
        ></v-swatches>
        <input type="hidden" name="color" :value="content"/>
    </div>
</template>



<script>
const VSwatches =
    () => import('vue-swatches');
//import VSwatches from 'vue-swatches'
import "vue-swatches/dist/vue-swatches.css"


export default {
    components: {VSwatches},
    name: "ColorPickerInput",
    props: {
        current: {
            default: "#F4F4F4",
        },
        value: {
            default: "#F4F4F4",
        },
        swatchSize: {
            default: 49
        },
        popoverTo: {
            type: String,
            default: 'right'
        },
        triggerStyle: {},
        height: {
            type: Number,
            default: 300
        }
    }
    ,
    methods: {
        handleInput (e) {
            this.$emit('input', this.content)
        }
    },
    data() {
        return {
            content: this.value
        }
    },
    watch: {
        value: function(newVal){
            this.content = newVal;
        },
        content: function(){
            //console.log(this.content);
        }
    }
}
</script>

<style scoped>
.vue-swatches__trigger {
    width: 19px !important;
    height: 19px !important;
    ;
}

</style>
